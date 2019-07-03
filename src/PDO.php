<?php

/*
 * This file is part of the guanguans/soar-php.
 *
 * (c) 琯琯 <yzmguanguan@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Guanguans\SoarPHP;

use PDO as BasePDO;
use PDOException;

class PDO extends BasePDO
{
    /**
     * @var \PDO|null
     */
    private $conn = null;

    /**
     * @var string
     */
    private $explainSkeleton = 'EOF
+----+-------------+---------+------------+------+---------------+------+---------+------+------+----------+-------+
| id | select_type | table   | partitions | type | possible_keys | key  | key_len | ref  | rows | filtered | Extra |
+----+-------------+---------+------------+------+---------------+------+---------+------+------+----------+-------+
| %s |      %s     |    %s   |     %s     |  %s  |       %s      |  %s  |   %s    |  %s  |  %s  |     %s   |   %s  |
+----+-------------+---------+------------+------+---------------+------+---------+------+------+----------+-------+
EOF';

    /**
     * PDO constructor.
     *
     * @param $dsn
     * @param null  $username
     * @param null  $password
     * @param array $options
     */
    public function __construct(
        $dsn,
        $username = null,
        $password = null,
        array $options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
    ) {
        parent::__construct($dsn, $username, $password, $options);

        try {
            $this->conn = new BasePDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * @param $sql
     *
     * @return string
     */
    public function getStrExplain($sql)
    {
        $explain = $this->getExplain($sql);

        return sprintf(
            $this->explainSkeleton,
            $explain['id'],
            $explain['select_type'],
            $explain['table'],
            $explain['partitions'],
            $explain['type'],
            $explain['possible_keys'],
            $explain['key'],
            $explain['key_len'],
            $explain['ref'],
            $explain['rows'],
            $explain['filtered'],
            $explain['Extra']
        );
    }

    /**
     * @param $sql
     *
     * @return mixed
     */
    public function getExplain($sql)
    {
        foreach ($this->conn->query('EXPLAIN '.$sql, PDO::FETCH_ASSOC) as $row) {
            return $row;
        }
    }

    /**
     * close PDO.
     */
    public function closeConnection()
    {
        $this->conn = null;
    }
}