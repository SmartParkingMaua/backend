SELECT `action` AS `action`,
COUNT(CASE WHEN MONTH(createdAt) = 1 THEN action END) AS `Janeiro`,
COUNT(CASE WHEN MONTH(createdAt) = 2 THEN action END) AS `Fevereiro`,
COUNT(CASE WHEN MONTH(createdAt) = 3 THEN action END) AS `Março`,
COUNT(CASE WHEN MONTH(createdAt) = 4 THEN action END) AS `Abril`,
COUNT(CASE WHEN MONTH(createdAt) = 5 THEN action END) AS `Maio`,
COUNT(CASE WHEN MONTH(createdAt) = 6 THEN action END) AS `Junho`,
COUNT(CASE WHEN MONTH(createdAt) = 7 THEN action END) AS `Julho`,
COUNT(CASE WHEN MONTH(createdAt) = 8 THEN action END) AS `Agosto`,
COUNT(CASE WHEN MONTH(createdAt) = 9 THEN action END) AS `Setembro`,
COUNT(CASE WHEN MONTH(createdAt) = 10 THEN action END) AS `Outubro`,
COUNT(CASE WHEN MONTH(createdAt) = 11 THEN action END) AS `Novembro`,
COUNT(CASE WHEN MONTH(createdAt) = 12 THEN action END) AS `Dezembro`
FROM `gates` AS `gate`
WHERE (`gate`.`idParking` = 1 OR `gate`.`idParking` = 2) AND `gate`.`createdAt` BETWEEN '1993-10-01 00:00:00' AND '1994-09-30 23:59:59'
GROUP BY `action`;
