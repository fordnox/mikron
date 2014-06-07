Mikron
======

[![Build Status](https://travis-ci.org/fordnox/mikron.svg)](https://travis-ci.org/fordnox/mikron)

Simple PHP PDO data mapper

Maps database rows to simple PHP object and **nothing more**.

Usage
======

Load database values to Order object. All public members of the class must be the same as field names in database.
Entity is identified by field "id"

Simple model class:

    class Entity
    {
        /** @field */
        public $id;

        /** @field */
        public $name;

        /** Not a field */
        public $anything;
    }

Maps database values to Entity class

    $mikron = new Mikron(new Pdo(...));
    $entity = $mikron->load('entity', 1);
    print $entity->id;
    print $entity->name;


Custom entity names and table names
======

Define custom function to map entities to table names and vice versa.
By default entity name is same as table name.

    $this->mikron->setNameResolver(function($type, $name) { return ucfirst($name); });
