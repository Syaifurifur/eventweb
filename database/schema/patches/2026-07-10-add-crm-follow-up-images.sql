ALTER TABLE `crm_lead_logs`
    ADD COLUMN IF NOT EXISTS `follow_up_image` VARCHAR(500) NULL AFTER `summary`;
