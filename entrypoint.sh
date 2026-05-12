#!/bin/bash
# Writes Docker env vars into an Apache SetEnv conf so PHP's getenv() works under mod_php.
cat > /etc/apache2/conf-enabled/docker-env.conf <<EOF
SetEnv DB_HOST       "${DB_HOST}"
SetEnv DB_PORT       "${DB_PORT}"
SetEnv DB_NAME       "${DB_NAME}"
SetEnv DB_USER       "${DB_USER}"
SetEnv DB_PASSWORD   "${DB_PASSWORD}"
SetEnv FACE_SERVICE_URL "${FACE_SERVICE_URL}"
SetEnv APP_ENV       "${APP_ENV}"
SetEnv GROQ_API_KEY_1 "${GROQ_API_KEY_1}"
SetEnv GROQ_API_KEY_2 "${GROQ_API_KEY_2}"
SetEnv GROQ_API_KEY_3 "${GROQ_API_KEY_3}"
SetEnv GROQ_API_KEY_4 "${GROQ_API_KEY_4}"
SetEnv GROQ_API_KEY_5 "${GROQ_API_KEY_5}"
SetEnv GEMINI_API_KEY "${GEMINI_API_KEY}"
EOF
exec apache2-foreground
