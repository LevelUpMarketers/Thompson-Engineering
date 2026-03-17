# k6 Load Testing (Initial Quiz Burst)

This directory contains a starter k6 script to simulate a burst of students logging in and completing an Initial quiz on a class page.

## Files

- `k6-initial-quiz-burst.js` — Simulates 20 concurrent students (configurable) each running one quiz attempt.
- `users.template.json` — Template credentials file. Copy and fill with real test users before running.

## Prerequisites

1. Install [k6](https://k6.io/).
2. Create dedicated student test accounts in WordPress (one per simulated student).
3. Copy `users.template.json` to a local, untracked file such as `users.local.json` and replace placeholder credentials.

## Run a realistic 20-student burst

```bash
cp load-tests/users.template.json load-tests/users.local.json
# edit load-tests/users.local.json with real credentials

CLASS_URL='https://many-side.flywheelsites.com/teqcidb-class/initialonlineclassapril1st20261773660431/' \
USERS_FILE='./load-tests/users.local.json' \
VUS=20 \
k6 run load-tests/k6-initial-quiz-burst.js
```

## Useful environment variables

- `CLASS_URL` (default is current provided class URL)
- `USERS_FILE` (default `./load-tests/users.local.json`)
- `VUS` (default `20`)
- `THINK_MIN_SECONDS` (default `4`)
- `THINK_MAX_SECONDS` (default `12`)
- `SAVE_EVERY_N_QUESTIONS` (default `3`)
- `MAX_DURATION` (default `20m`)

## Production cadence alignment

- Front-end quiz autosave cadence now defaults to 12 seconds (`autosaveIntervalMs = 12000`) to reduce routine autosave request volume while preserving existing reliability triggers (dirty-state gating, boundary saves, visibility/blur saves, beforeunload beacon saves, and full final submit payloads).
- This k6 script defaults to `SAVE_EVERY_N_QUESTIONS=3` to better mirror that less-chatty production save cadence.

## When to tighten autosave cadence intentionally

Temporarily lower save cadence (for example `SAVE_EVERY_N_QUESTIONS=1` or `2`) when you explicitly want to stress quiz save endpoints, simulate worst-case autosave chatter, or run regression checks around resume/save durability under frequent writes. Return to the default cadence for standard load characterization.

## Notes

- The script performs a WordPress login, loads the class page, parses `data-quiz-runtime`, then calls:
  - `POST /wp-json/teqcidb/v1/quiz/progress`
  - `POST /wp-json/teqcidb/v1/quiz/submit`
- Keep test users isolated from real students.
- Start with staging or low-risk windows on production-like infrastructure.
