# Repository Guidelines

## Project Structure & Module Organization
`src/` holds all Parsable File Multi-Tool PHP services under the `Misery\` namespace, grouped by domain (actions, converters, directives). Integration-ready examples and sample payloads live in `examples/`, while reusable YAML templates live in `config/`. Specs are under `tests/` with mirrored namespaces (`Tests\Misery`). CLI entrypoints (`bin/docker/console`, `bin/docker/composer`) and helper scripts in `scripts/` compose the local tooling. Keep transient outputs inside `var/` so the repository stays clean.

## Build, Test, and Development Commands
- `bin/docker/composer install` — install dependencies inside the project Docker image (preferred so extensions match production).
- `bin/docker/console transformation --file examples/transformation.yaml --source data/in --workpath var/out` — run a sample pipeline; swap paths when validating new directives.
- `bin/docker/php` — general PHP entrypoint via Docker (`docker-compose run -u 1000:1000 --rm fpm php ...`); use this wrapper to execute ad-hoc PHP commands safely.
- `bin/docker/phpunit` — run PHPUnit inside the Docker image (`bin/docker/php vendor/bin/phpunit ...`).
- `bin/docker/composer` — composer wrapper pinned to the container PHP (`bin/docker/php -d memory_limit=4G /usr/bin/composer ...`).
- `bin/docker/python` — Python wrapper inside the container (`docker-compose run -u 1000:1000 --rm fpm /opt/venv/bin/python3 ...`), using the venv interpreter from `docker/fpm/Dockerfile`.
- `bin/docker/run_example.sh` — scaffolds example folders and runs `bin/docker/console` for a given project (`PROJECT` env var).
- `bin/docker/composer test` — run the default pipeline: PHPUnit, then PHPStan on `src/` and `tests/`.
- `bin/docker/composer unit-test` / `composer sa-test` — run only PHPUnit or only static analysis when iterating quickly.

## Coding Style & Naming Conventions
Follow PSR-12: 4-space indentation, trailing commas in multi-line arrays, and one class per file. Class names are StudlyCase, services end with their concern (e.g., `*Converter`, `*Action`), and configuration DTOs end in `Config`. Interfaces live beside implementations and use `Interface` suffix. Prefer constructor promotion and typed properties (PHP ≥8.1).

## Testing Guidelines
Add PHPUnit specs mirroring the production namespace (`tests/Misery/Foo/BarTest.php`). Name tests for intent (`testTransformsFileWithCustomAction`). For pipelines, provide representative fixtures under `tests/fixtures/` or `examples/` to keep cases reproducible. Every bug fix needs a regression test plus a static-analysis-safe path. Use `composer github-test` when preparing CI submissions; it excludes heavy performance groups so results match GitHub Actions.

## Commit & Pull Request Guidelines
Commit subjects follow `verb: context` (see `git log`, e.g., `fix: refresh token clear invalidation`). Keep commits scoped to a single concern and reference Jira/issue IDs in the body when applicable. Pull requests should include: problem statement, implementation notes (mention new directives, console flags, or config keys), testing evidence (`composer test` output), and screenshots/log excerpts for data transformations. Link related docs or example files so reviewers can reproduce results quickly.

## Security & Configuration Tips
Never commit real credentials; store sample accounts in `config/*.dist`. Secrets for Akeneo or other APIs belong in `.env.local` or your orchestration layer. When sharing configs, scrub `account` blocks and redact JWT payloads. Validate new converters with sanitized fixtures before touching production data.
