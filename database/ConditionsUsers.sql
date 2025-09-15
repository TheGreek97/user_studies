SELECT
    ( SELECT COUNT(*) FROM users ) AS TotalUsers,
    ( SELECT COUNT(*) FROM users WHERE warning_type = 'popup_email' AND show_explanation=0) AS ActiveNoExp,
    ( SELECT COUNT(*) FROM users WHERE warning_type = 'popup_email' AND show_explanation=1) AS ActiveExp,
    ( SELECT COUNT(*) FROM users WHERE warning_type = 'popup_link' AND show_explanation=0) AS ActiveAfterNoExp,
    ( SELECT COUNT(*) FROM users WHERE warning_type = 'popup_link' AND show_explanation=1) AS ActiveAfterExp,
    ( SELECT COUNT(*) FROM users WHERE warning_type = 'tooltip' AND show_explanation=0) AS TooltipNoExp,
    ( SELECT COUNT(*) FROM users WHERE warning_type = 'tooltip' AND show_explanation=1) AS TooltipExp
FROM users LIMIT 1
