ALTER TABLE `event_checklist_items`
    ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `status`;

UPDATE `event_checklist_templates`
SET `is_active` = CASE
    WHEN `name` IN ('Proposal', 'Flyer', 'Spanduk', 'Backdrop', 'Teaser', 'Release Pra Event') THEN 1
    ELSE 0
END,
`updated_at` = NOW()
WHERE `phase` = 'Pra Event';

UPDATE `event_checklist_templates`
SET `sort_order` = CASE `name`
    WHEN 'Proposal' THEN 10
    WHEN 'Flyer' THEN 20
    WHEN 'Spanduk' THEN 30
    WHEN 'Backdrop' THEN 40
    WHEN 'Teaser' THEN 50
    WHEN 'Release Pra Event' THEN 60
    ELSE `sort_order`
END,
`updated_at` = NOW()
WHERE `phase` = 'Pra Event'
  AND `name` IN ('Proposal', 'Flyer', 'Spanduk', 'Backdrop', 'Teaser', 'Release Pra Event');

UPDATE `event_checklist_items`
SET `is_active` = CASE
    WHEN `name` IN ('Proposal', 'Flyer', 'Spanduk', 'Backdrop', 'Teaser', 'Release Pra Event') THEN 1
    ELSE 0
END,
`updated_at` = NOW()
WHERE `phase` = 'Pra Event';
