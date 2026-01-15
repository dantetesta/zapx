-- ============================================
-- Seed: Usuário Admin Padrão (Idempotente)
-- Autor: Dante Testa (https://dantetesta.com.br)
-- Data: 2026-01-14 20:35:00
-- ============================================
-- Senha padrão: Admin@123 (bcrypt cost 12)
-- ⚠️ ALTERE A SENHA APÓS O PRIMEIRO LOGIN!

INSERT INTO users (name, email, password, is_admin, message_limit, messages_sent, limit_reset_date)
SELECT 'Administrador', 'admin@dantetesta.com.br', '$2y$12$LQNJMPjW0e8mMX9vQ9e0eO8K5c5g5g5g5g5g5g5g5g5g5g5g5g5g5', 1, 999999, 0, CURDATE()
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@dantetesta.com.br'
);
