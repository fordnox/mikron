mikron
======

PHP Simple PDO data mapper

Maps database rows to simple PHP object and **nothing more**.

Usage
======

Load database values to Order object. All public members of the class must be the same as field names in database.
Entity is identified by field "id"

Simple model class:

    class Entity
    {
        public $id;
        public $name;
    }

Map database values to Entity class

    $mikron = new Mikron(new Pdo(...));
    $entity = $mikron->load('entity', 1);
    print $entity->id;
    print $entity->name;
