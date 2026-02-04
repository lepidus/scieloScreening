# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SciELO Screening Plugin for OPS 3.5 (Open Preprint Systems). A PKP generic plugin that performs automated verifications on author submissions and displays status in the editorial workflow.

**Stack:** PHP 8.0+ backend, Vue 3 frontend, Smarty templates, Vite bundler

## Common Commands

```bash
# Frontend development (watch mode)
npm run dev

# Production build
npm run build

# PHP code style fixing
php-cs-fixer fix

# Run PHP unit tests (from OPS root directory)
./lib/pkp/lib/vendor/bin/phpunit plugins/generic/scieloScreening/tests/

# Run specific unit test
./lib/pkp/lib/vendor/bin/phpunit plugins/generic/scieloScreening/tests/ScreeningCheckerTest.php

# Run Cypress tests (from OPS root directory)
npx cypress run --config integrationFolder=plugins/generic/scieloScreening/cypress/tests
```

## System Requirements

- `poppler-utils` package required for `pdftotext` command (used by DocumentChecker)

## Architecture

**Plugin Entry Point:** `ScieloScreeningPlugin.php` - Registers hooks for form validation, template rendering, schema modification, and API routing.

**Core Classes (classes/):**
- `ScreeningExecutor` - Orchestrates all screening checks, returns comprehensive status object
- `ScreeningChecker` - Validation engine for affiliations, ORCID, uppercase names, PDF count
- `DocumentChecker` - PDF text extraction and ORCID detection using pdftotext
- `OrcidClient` - OAuth 2.0 client for ORCID API (Public/Sandbox/Member endpoints)
- `APIKeyEncryption` - Encrypts/decrypts ORCID credentials at rest

**Frontend (resources/js/):**
- Vue 3 Composition API with `<script setup>`
- Integrates with PKP UI Module (`useLocalize`, `useUrl`, `useFetch`)
- Components registered via PKP registry system

**API Endpoint:**
- `GET /api/v1/submissions/{submissionId}/screening` - Returns all screening statuses

**Hook Integration Points:**
- `Submission::validateSubmit` / `Publication::validatePublish` - Form validation
- `Template::SubmissionWizard::Section::Review` - Template rendering
- `Schema::get::publication` - Schema modification
- `Settings::Workflow::listScreeningPlugins` - Workflow UI registration

## Testing

- **PHP Unit Tests:** `tests/` directory with test assets in `tests/assets/`
- **Cypress E2E:** `cypress/tests/` - Plugin setup, submission wizard, workflow features
- **CI:** GitLab CI with unit tests, Cypress tests, and PHP-CS-Fixer checks

## Locale

Translations in `locale/` (en, es, pt_BR). Backend locale keys registered in `registry/uiLocaleKeysBackend.json`.
