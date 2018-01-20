<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 * @MongoDB\Index(keys={"points"="desc", "played"="asc"})
 */
class User
{
    /**
     * @var int
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     * @MongoDB\Index(unique=true, sparse=true, order="asc")
     */
    protected $login;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $pass;

    /**
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $played = 0;

    /**
     * @var int
     * @MongoDB\Field(type="int")
     */
    protected $points = 0;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param string $pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    /**
     * @return int
     */
    public function getPlayed()
    {
        return $this->played;
    }

    /**
     * @param int $played
     */
    public function setPlayed($played)
    {
        $this->played = $played;
    }

    /**
     * Increment played count by one
     */
    public function incrementPlayed()
    {
        $this->played++;
    }

    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param int $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }

    /**
     * @param int $points
     */
    public function addPoints($points)
    {
        $this->points += $points;
    }
}