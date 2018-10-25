SELECT acao AS action,
    COUNT(CASE WHEN MINUTE(timestamp) BETWEEN 0 AND 4 THEN acao END) '5m',
    COUNT(CASE WHEN MINUTE(timestamp) BETWEEN 5 AND 9 THEN acao END) '10m',
    COUNT(CASE WHEN MINUTE(timestamp) BETWEEN 10 AND 14 THEN acao END) '15m',
    COUNT(CASE WHEN MINUTE(timestamp) BETWEEN 15 AND 19 THEN acao END) '20m',
    COUNT(CASE WHEN MINUTE(timestamp) BETWEEN 20 AND 24 THEN acao END) '25m',
    COUNT(CASE WHEN MINUTE(timestamp) BETWEEN 25 AND 29 THEN acao END) '30m',
    COUNT(CASE WHEN MINUTE(timestamp) BETWEEN 30 AND 34 THEN acao END) '35m',
    COUNT(CASE WHEN MINUTE(timestamp) BETWEEN 35 AND 39 THEN acao END) '40m',
    COUNT(CASE WHEN MINUTE(timestamp) BETWEEN 40 AND 44 THEN acao END) '45m',
    COUNT(CASE WHEN MINUTE(timestamp) BETWEEN 45 AND 49 THEN acao END) '50m',
    COUNT(CASE WHEN MINUTE(timestamp) BETWEEN 50 AND 54 THEN acao END) '55m',
    COUNT(CASE WHEN MINUTE(timestamp) BETWEEN 55 AND 59 THEN acao END) '60m'
FROM tbl_portaria WHERE idportaria=1 AND DATE(timestamp) = DATE(FROM_UNIXTIME(1508276400))
AND HOUR(timestamp) = HOUR(FROM_UNIXTIME(1508276400)) GROUP BY acao
