# BlueHR

BlueHR Human Resource Platform

## Install
1. Extract to `htdocs/bluehr`.
2. Open `http://localhost/bluehr/install.php`.
3. Fill database and admin user.
4. Login at `http://localhost/bluehr/public/login`.
5. Delete `install.php` after installation.

## Fallback URL
If Apache rewrite is disabled, use `http://localhost/bluehr/public/index.php/login`.

## Architecture
This application uses modular Core + Modules structure. Every module has operational records, module settings, reports foundation, permissions foundation, and audit logs.

## BlueHub Integration
This app is designed to be integrated via BlueHub, not direct uncontrolled pairwise integration.
