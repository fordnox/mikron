<?php
/**
 * Mikron
 *
 * Copyright (c) 2014, Andrius Putna <fordnox@gmail.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

class Mikron
{
    /**
     * @var \PDO
     */
    protected $pdo = null;

    /**
     * @var Closure
     */
    protected $closure = null;

    public function __construct(\Pdo $pdo)
    {
        $this->pdo = $pdo;
    }

    public function execute($sql, $values = array())
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function load($type, $id)
    {
        return $this->findOne($type, " id = :id", array('id'=>$id));
    }

    public function store($object)
    {
        if(!is_object($object)) {
            throw new Exception('Invalid Entity. Should be object');
        }

        $table = $this->getTableName($object);
        $values = array();
        $keys = array_keys(get_class_vars(get_class($object)));
        foreach($keys as $k) {
            $values[$k] = $object->{$k};
        }
        if(is_null($object->id)) {
            $str = '`'.implode('`, `', $keys).'`';
            $str2 = ':'.implode(', :', $keys);
            $sql="INSERT INTO $table ($str) VALUES ($str2)";
            $this->execute($sql, $values);
            $object->id = $this->lastInsertId();
        } else {
            $str = '';
            foreach($keys as $k) {
                if($k == 'id') continue;
                $str .= " $k=:$k,";
            }
            $str = rtrim($str, ',');
            $sql="UPDATE $table SET $str WHERE id = :id";
            $this->execute($sql, $values);
        }
        return $object;
    }

    public function findOne($type, $sql = null, $values = array())
    {
        $table = $this->getTableName($type);
        $class = $this->getClassName($type);
        $sql = "SELECT * FROM $table WHERE $sql LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode( \PDO::FETCH_CLASS, $class );
        $stmt->execute($values);
        return $stmt->fetch();
    }

    public function findAll($type, $sql = null, $values = array())
    {
        $table = $this->getTableName($type);
        $class = $this->getClassName($type);
        $sql = "SELECT * FROM $table WHERE $sql LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode( \PDO::FETCH_CLASS, $class );
        $stmt->execute($values);
        return $stmt->fetchAll();
    }

    public function trash($object)
    {
        if(!is_object($object)) {
            throw new Exception('Invalid Entity. Should be object');
        }

        if(is_null($object->id)) {
            throw new \Exception('Invalid Entity id');
        }
        $table = $this->getTableName($object);
        $sql="DELETE FROM $table WHERE id = :id";
        return $this->execute($sql, array('id'=>$object->id));
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function getAll($sql, $values = array() )
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode( \PDO::FETCH_ASSOC);
        $stmt->execute($values);
        return $stmt->fetchAll();
    }

    public function getCell($sql, $values = array() )
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode( \PDO::FETCH_ASSOC);
        $stmt->execute($values);
        return $stmt->fetchColumn();
    }

    public function getRow($sql, $values = array() )
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode( \PDO::FETCH_ASSOC);
        $stmt->execute($values);
        return $stmt->fetch();
    }

    public function setNameResolver(Closure $function)
    {
        $this->closure = $function;
    }

    protected function getClassName($type)
    {
        if($this->closure) {
            $factory = $this->closure;
            return $factory('entity', $type);
        }

        return ucfirst($type);
    }

    protected function getTableName($type)
    {
        if($this->closure) {
            $factory = $this->closure;
            return $factory('table', $type);
        }

        if(is_object($type)) {
            return strtolower(get_class($type));
        } else {
            return strtolower($type);
        }
    }
}
