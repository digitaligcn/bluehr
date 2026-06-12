# Modular Architecture

BlueHR final harus dikembangkan secara modular.

Prinsip:

1. Core tidak boleh diisi business logic spesifik.
2. Fitur baru dibuat sebagai module baru atau extension module.
3. Route, controller, view, service, permission, migration, setting dipisahkan per module.
4. Integrasi eksternal seperti Accurate, Google Drive, WhatsApp, dan AI diperlakukan sebagai module/plugin.
5. Shared logic masuk ke `app/Services` hanya jika benar-benar reusable lintas module.
