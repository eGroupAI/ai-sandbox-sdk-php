# 30-Day Optimization Plan (PHP SDK)

## Outcome Target

- Deliver a low-touch, production-ready PHP SDK with complete streaming support and measurable quality gates.
- Keep first API success under 10 minutes and first SSE integration under 30 minutes.

## P0 (Day 1-14): Reliability and Contract Hardening

| Workstream | Task | Files | Acceptance |
| --- | --- | --- | --- |
| API Contract Alignment | Ensure endpoint paths/methods match backend contract and integration docs | `src/AiSandboxClient.php`, `openapi/ai-sandbox-v1.yaml`, `docs/INTEGRATION.md` | 11 API operations validated with no mismatch |
| Safe Retry Policy | Keep retries safe by default for non-idempotent writes and document strategy | `src/AiSandboxClient.php`, `README.md` | No duplicate write operations in fault simulation |
| SSE Helper Standard | Keep `sendChatStream(...)` stable with parser regression checks | `src/AiSandboxClient.php`, `examples/quickstart.php` | SSE parser reliably handles `[DONE]` and chunk lines |
| QA Baseline | Add tests for retry, SSE parse, and JSON error behavior | `src/*.php`, `tests/*` (new), `composer.json` | CI tests pass with critical-path coverage target |
| CI/CD Guardrails | Add workflow for lint/test/package validation | `.github/workflows/ci.yml` (new), `composer.json` | Required checks block failing PRs |

## P1 (Day 15-30): Developer Experience and Growth

| Workstream | Task | Files | Acceptance |
| --- | --- | --- | --- |
| Example Expansion | Upgrade quickstart to full flow (agent -> channel -> SSE -> KB) | `examples/quickstart.php`, `README.md` | Example runs with env vars only |
| Visual Docs Upgrade | Add troubleshooting matrix and support playbook | `README.md`, `docs/INTEGRATION.md` | Faster customer onboarding in pilot phase |
| Release Quality | Add release checklist and compatibility notes | `CHANGELOG.md`, `CONTRIBUTING.md` | Versioned notes included for every release |
| Security Posture | Add dependency audit and secret scan in workflow | `.github/workflows/ci.yml`, `SECURITY.md` | No unresolved high-severity issue at release gate |

## Language File Checklist

- `README.md`
- `docs/INTEGRATION.md`
- `docs/30D_OPTIMIZATION_PLAN.md`
- `src/AiSandboxClient.php`
- `src/ApiException.php`
- `examples/quickstart.php`
- `openapi/ai-sandbox-v1.yaml`
- `composer.json`
- `CHANGELOG.md`
- `CONTRIBUTING.md`
- `SECURITY.md`

## Definition of Done (DoD)

- [ ] 11/11 API operations pass production integration validation.
- [ ] SSE helper returns chunk list and stops on `[DONE]` in regression tests.
- [ ] Retry defaults prevent duplicate non-idempotent writes.
- [ ] CI pipeline enforces lint + test + package checks on every PR.
- [ ] README quickstart runs from clean environment using only required env vars.
