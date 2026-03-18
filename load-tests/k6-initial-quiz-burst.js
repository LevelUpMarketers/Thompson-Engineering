import http from 'k6/http';
import { check, fail, sleep } from 'k6';
import { SharedArray } from 'k6/data';
import { Trend } from 'k6/metrics';

const quizProgressDuration = new Trend('quiz_progress_duration');
const quizSubmitDuration = new Trend('quiz_submit_duration');

const classUrl = __ENV.CLASS_URL || 'https://many-side.flywheelsites.com/teqcidb-class/initialonlineclassapril1st20261773660431/';
const usersFile = __ENV.USERS_FILE || './load-tests/users.local.json';
const vus = Number(__ENV.VUS || 20);
const thinkMin = Number(__ENV.THINK_MIN_SECONDS || 4);
const thinkMax = Number(__ENV.THINK_MAX_SECONDS || 12);
const saveEvery = Number(__ENV.SAVE_EVERY_N_QUESTIONS || 3);

const users = new SharedArray('teqcidb-users', function () {
  return JSON.parse(open(usersFile));
});

if (!Array.isArray(users) || users.length < vus) {
  fail(`Users file ${usersFile} must contain at least ${vus} accounts. Found: ${Array.isArray(users) ? users.length : 'invalid file'}`);
}

export const options = {
  scenarios: {
    initial_quiz_burst: {
      executor: 'per-vu-iterations',
      vus,
      iterations: 1,
      maxDuration: __ENV.MAX_DURATION || '20m',
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.02'],
    http_req_duration: ['p(95)<1500'],
    quiz_progress_duration: ['p(95)<800'],
    quiz_submit_duration: ['p(95)<2000'],
  },
};

function decodeHtmlEntities(value) {
  if (!value) return '';
  return String(value)
    .replace(/&quot;/g, '"')
    .replace(/&#039;/g, "'")
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
}

function extractRuntime(html) {
  const appTagMatch = html.match(/<[^>]*id=["']teqcidb-class-quiz-app["'][^>]*>/i);
  if (!appTagMatch) {
    fail('Could not find #teqcidb-class-quiz-app on class page.');
  }

  const appTag = appTagMatch[0];
  const runtimeMatch = appTag.match(/data-quiz-runtime=(['"])(.*?)\1/i);
  if (!runtimeMatch) {
    fail('Could not find data-quiz-runtime on class page.');
  }

  try {
    return JSON.parse(decodeHtmlEntities(runtimeMatch[2]));
  } catch (error) {
    fail(`Unable to parse data-quiz-runtime JSON: ${error.message}`);
  }
}

function randomThink() {
  const floor = Math.max(0, thinkMin);
  const ceil = Math.max(floor, thinkMax);
  const seconds = floor + Math.random() * (ceil - floor);
  sleep(seconds);
}

function login(baseUrl, classPageUrl, username, password) {
  const form = {
    log: username,
    pwd: password,
    'wp-submit': 'Log In',
    redirect_to: classPageUrl,
    testcookie: '1',
    rememberme: 'forever',
  };

  const response = http.post(`${baseUrl}/wp-login.php`, form, {
    redirects: 0,
    tags: { endpoint: 'wp_login' },
  });

  check(response, {
    'login accepted': (r) => r.status === 302 || r.status === 200,
  });
}

function getBaseUrl(url) {
  const parsed = new URL(url);
  return `${parsed.protocol}//${parsed.host}`;
}

function buildAnswersPayload(runtime, answeredCount) {
  const answers = {};
  const questions = Array.isArray(runtime.questions) ? runtime.questions : [];

  for (let i = 0; i < answeredCount; i += 1) {
    const q = questions[i];
    if (!q || !q.id) continue;

    const firstChoice = Array.isArray(q.choices) && q.choices.length ? q.choices[0] : null;
    if (!firstChoice || typeof firstChoice.value === 'undefined') continue;

    answers[String(q.id)] = [String(firstChoice.value)];
  }

  return answers;
}

export default function () {
  const user = users[__VU - 1];
  if (!user || !user.username || !user.password) {
    fail(`Missing user credentials for VU index ${__VU}.`);
  }

  const baseUrl = getBaseUrl(classUrl);
  login(baseUrl, classUrl, user.username, user.password);

  const classPage = http.get(classUrl, { tags: { endpoint: 'class_page' } });
  check(classPage, {
    'class page loaded': (r) => r.status === 200,
  });

  const runtime = extractRuntime(classPage.body);
  const restNonce = runtime.restNonce;
  const restUrl = String(runtime.restUrl || '').replace(/\/$/, '');
  const quiz = runtime.quiz || {};
  const questions = Array.isArray(runtime.questions) ? runtime.questions : [];

  if (!restNonce || !restUrl || !quiz.id || !quiz.classId || !questions.length) {
    fail('Class runtime missing restNonce/restUrl/quiz metadata/questions.');
  }

  let attemptId = (runtime.attempt && runtime.attempt.id) || 0;

  for (let idx = 0; idx < questions.length; idx += 1) {
    randomThink();

    const answeredCount = idx + 1;
    if (answeredCount % saveEvery !== 0 && answeredCount !== questions.length) {
      continue;
    }

    const progressPayload = {
      quiz_id: quiz.id,
      class_id: quiz.classId,
      attempt_id: attemptId,
      current_question_index: idx,
      answers: buildAnswersPayload(runtime, answeredCount),
    };

    const progressRes = http.post(`${restUrl}/quiz/progress`, JSON.stringify(progressPayload), {
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': restNonce,
      },
      tags: { endpoint: 'quiz_progress' },
    });

    check(progressRes, {
      'quiz progress status 200': (r) => r.status === 200,
      'quiz progress ok=true': (r) => {
        try {
          return JSON.parse(r.body).ok === true;
        } catch (_error) {
          return false;
        }
      },
    });

    quizProgressDuration.add(progressRes.timings.duration);

    let progressJson;
    try {
      progressJson = JSON.parse(progressRes.body);
    } catch (_error) {
      fail('Progress response was not valid JSON.');
    }

    attemptId = Number(progressJson.attempt_id || attemptId || 0);
  }

  const submitPayload = {
    quiz_id: quiz.id,
    class_id: quiz.classId,
    attempt_id: attemptId,
    current_question_index: Math.max(0, questions.length - 1),
    answers: buildAnswersPayload(runtime, questions.length),
  };

  const submitRes = http.post(`${restUrl}/quiz/submit`, JSON.stringify(submitPayload), {
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': restNonce,
    },
    tags: { endpoint: 'quiz_submit' },
  });

  check(submitRes, {
    'quiz submit status 200': (r) => r.status === 200,
    'quiz submit ok=true': (r) => {
      try {
        return JSON.parse(r.body).ok === true;
      } catch (_error) {
        return false;
      }
    },
  });

  quizSubmitDuration.add(submitRes.timings.duration);
}
