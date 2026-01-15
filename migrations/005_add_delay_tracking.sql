-- Migração: Adicionar campos para tracking de delay
-- Autor: Dante Testa (https://dantetesta.com.br)
-- Data: 2026-01-15 19:20:00

ALTER TABLE dispatch_campaigns 
ADD COLUMN next_delay INT DEFAULT NULL COMMENT 'Próximo delay sorteado em segundos',
ADD COLUMN next_send_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Timestamp do próximo envio';
