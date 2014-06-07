<?php

class Entity
{
    /** @primary */
    public $id;

    /** @field */
    public $name;

    public $skip;
}

class MikronTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mikron
     */
    protected $mikron;

    public function setup()
    {
        $pdo = new \Pdo(
            $GLOBALS['DB_DSN'],
            $GLOBALS['DB_USER'],
            $GLOBALS['DB_PASSWD'],
            array(
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY         => true,
                \PDO::ATTR_ERRMODE                          => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE               => \PDO::FETCH_ASSOC,
            )
        );

        $sql="
            DROP TABLE IF EXISTS `entity`;
            CREATE TABLE `entity` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(225) DEFAULT NULL,
              PRIMARY KEY (`id`)
            );
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        require_once 'Mikron.php';
        $this->mikron = new Mikron($pdo);
    }

    public function testExecute()
    {
        $result = $this->mikron->execute("SELECT 2");
        $this->assertEquals(2, $result);

        $result = $this->mikron->execute("SELECT :num", array('num'=>3));
        $this->assertEquals(3, $result);
    }

    public function testLoad()
    {
        $newEntity = new Entity();
        $newEntity->name = 'Andrius Putna';
        $this->mikron->store($newEntity);

        $entity = $this->mikron->load("entity", 1);
        $this->assertInstanceOf('Entity', $entity);
        $this->assertEquals(1, $entity->id);
        $this->assertEquals('Andrius Putna', $entity->name);
    }

    public function testStoreInsert()
    {
        $newEntity = new Entity();
        $newEntity->name = 'New Name';
        $EntityWithId = $this->mikron->store($newEntity);
        $this->assertFalse(is_null($EntityWithId->id));
        $this->assertEquals($EntityWithId->id, $this->mikron->lastInsertId());
    }

    public function testStoreUpdate()
    {
        $newEntity = new Entity();
        $newEntity->name = 'New Name';
        $entity = $this->mikron->store($newEntity);
        $this->assertEquals(1, $entity->id);

        $entity->name = "Andrius Putna";
        $entity = $this->mikron->store($entity);
        $this->assertEquals(1, $entity->id);
    }

    public function testFindOne()
    {
        $newEntity = new Entity();
        $newEntity->name = 'Andrius Putna';
        $this->mikron->store($newEntity);

        $entity = $this->mikron->findOne("entity", "name = :name", array('name'=>'Andrius Putna'));
        $this->assertEquals(1, $entity->id);
    }

    public function testFindAll()
    {
        $entities = $this->mikron->findAll("entity", "1");
        foreach($entities as $entity) {
            $this->assertInstanceOf('Entity', $entity);
        }
    }

    public function testTrash()
    {
        $newEntity = new Entity();
        $newEntity->name = 'Andrius Putna';
        $this->mikron->store($newEntity);

        $entity = $this->mikron->load("entity", 1);
        $bool = $this->mikron->trash($entity);
        $this->assertTrue($bool);
    }

    public function testGetAll()
    {
        $newEntity = new Entity();
        $newEntity->name = 'Name';
        $this->mikron->store($newEntity);

        $newEntity = new Entity();
        $newEntity->name = 'Name';
        $this->mikron->store($newEntity);

        $list = $this->mikron->getAll("SELECT * FROM entity WHERE 1");
        foreach($list as $item) {
            $this->assertInternalType('array', $item);
            $this->assertEquals('Name', $item['name']);
        }
    }

    public function testGetRow()
    {
        $newEntity = new Entity();
        $newEntity->name = 'Name';
        $this->mikron->store($newEntity);

        $item = $this->mikron->getRow("SELECT * FROM entity WHERE id = 1");
        $this->assertInternalType('array', $item);
        $this->assertEquals('Name', $item['name']);
    }

    public function testGetCell()
    {
        $cell = $this->mikron->getCell("SELECT 44");
        $this->assertEquals(44, $cell);
    }

    /*

    public function testGetTableName()
    {
        $name = "Title";
        $this->mikron->setNameResolver(function($type, $name) { return $name; });
        $newName = $this->mikron->getTableName($name);
        $this->assertEquals($name, $newName);
    }

    public function testGetEntityName()
    {
        $name = "title";
        $this->mikron->setNameResolver(function($type, $name) { return ucfirst($name); });
        $newName = $this->mikron->getClassName($name);
        $this->assertEquals('Title', $newName);
    }

    */
}
