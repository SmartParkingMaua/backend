SELECT `action` AS `action`,
COUNT(CASE WHEN MINUTE(createdAt) BETWEEN 0 AND 4 THEN action END) AS `0m`,
COUNT(CASE WHEN MINUTE(createdAt) BETWEEN 5 AND 9 THEN action END) AS `5m`,
COUNT(CASE WHEN MINUTE(createdAt) BETWEEN 10 AND 14 THEN action END) AS `10m`,
COUNT(CASE WHEN MINUTE(createdAt) BETWEEN 15 AND 19 THEN action END) AS `15m`,
COUNT(CASE WHEN MINUTE(createdAt) BETWEEN 20 AND 24 THEN action END) AS `20m`,
COUNT(CASE WHEN MINUTE(createdAt) BETWEEN 25 AND 29 THEN action END) AS `25m`,
COUNT(CASE WHEN MINUTE(createdAt) BETWEEN 30 AND 34 THEN action END) AS `30m`,
COUNT(CASE WHEN MINUTE(createdAt) BETWEEN 35 AND 39 THEN action END) AS `35m`,
COUNT(CASE WHEN MINUTE(createdAt) BETWEEN 40 AND 44 THEN action END) AS `40m`,
COUNT(CASE WHEN MINUTE(createdAt) BETWEEN 45 AND 49 THEN action END) AS `45m`,
COUNT(CASE WHEN MINUTE(createdAt) BETWEEN 50 AND 54 THEN action END) AS `50m`,
COUNT(CASE WHEN MINUTE(createdAt) BETWEEN 55 AND 59 THEN action END) AS `55m`
FROM `gates` AS `gate`
WHERE (`gate`.`idParking` = 1 OR `gate`.`idParking` = 2) AND `gate`.`createdAt` BETWEEN '1993-12-28 04:00:00' AND '1993-10-28 04:59:59'
GROUP BY `action`;