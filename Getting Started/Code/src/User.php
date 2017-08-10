<?php

use Doctrine\Common\Collections\ArrayCollection;

/**
 * src/User.php
 *
 * @author: Leo
 * @version: 1.0
 *
 * @Entity
 * @Table(name="users")
 */
class User
{
    /**
     * @var integer
     * @Id
     * @GeneratedValue
     * @Column(type="integer", name="user_id")
     */
    protected $userID;

    /**
     * @var string
     * @Column(type="string", name="user_name", length=45)
     */
    protected $userName;

    /**
     * 指派给我的 Bug
     *
     * @var Bug[] An ArrayCollection of Bug objects
     * @OneToMany(targetEntity="Bug", mappedBy="engineer")
     */
    protected $assignedBugs;

    /**
     * 我报告的 Bug
     *
     * @var Bug[] An ArrayCollection of Bug objects
     * @OneToMany(targetEntity="Bug", mappedBy="reporter")
     */
    protected $reportedBugs;


    public function __construct()
    {
        $this->reportedBugs = new ArrayCollection();
        $this->assignedBugs = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * 添加一个我报告的 Bug
     *
     * @param Bug $bug
     */
    public function addReportedBug(Bug $bug)
    {
        $this->reportedBugs[] = $bug;
    }

    /**
     * @return Bug[]|ArrayCollection
     */
    public function getReportedBugs()
    {
        return $this->reportedBugs;
    }

    /**
     * 接收一个指派给我的 Bug
     *
     * @param Bug $bug
     */
    public function assignedToBug(Bug $bug)
    {
        $this->assignedBugs[] = $bug;
    }

    /**
     * @return Bug[]|ArrayCollection
     */
    public function getAssignedBugs()
    {
        return $this->assignedBugs;
    }
}