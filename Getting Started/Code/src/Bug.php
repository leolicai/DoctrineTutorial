<?php
use Doctrine\Common\Collections\ArrayCollection;

/**
 * src/Bug.php
 *
 * @author: Leo
 * @version: 1.0
 *
 * @Entity
 * @Table(name="bugs")
 */
class Bug
{
    /**
     * @var integer
     * @Id
     * @GeneratedValue
     * @Column(type="integer", name="bug_id")
     */
    protected $bugID;

    /**
     * @var string
     * @Column(type="string", name="bug_description", length=255)
     */
    protected $bugDescription;

    /**
     * @var integer
     * @Column(type="integer", name="bug_status")
     */
    protected $bugStatus;

    /**
     * @var DateTime
     * @Column(type="datetime", name="bug_created")
     */
    protected $bugCreated;

    /**
     * 处理 Bug 的工程师
     *
     * @var User
     * @ManyToOne(targetEntity="User", inversedBy="assignedBugs")
     * @JoinColumn(name="bug_assigned_to", referencedColumnName="user_id")
     */
    protected $engineer;

    /**
     * 报告 Bug 的用户
     *
     * @var User
     * @ManyToOne(targetEntity="User", inversedBy="reportedBugs")
     * @JoinColumn(name="bug_reported_by", referencedColumnName="user_id")
     */
    protected $reporter;

    /**
     * 出现 Bug 的产品集合
     *
     * @var Product[] An ArrayCollection of Product objects
     * @ManyToMany(targetEntity="Product", inversedBy="products")
     * @JoinTable(
     *     name="relation_bug_product",
     *     joinColumns={@JoinColumn(name="relation_bug_id", referencedColumnName="bug_id")},
     *     inverseJoinColumns={@JoinColumn(name="relation_product_id", referencedColumnName="product_id")}
     * )
     */
    protected $products;


    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getBugID()
    {
        return $this->bugID;
    }

    /**
     * @return string
     */
    public function getBugDescription()
    {
        return $this->bugDescription;
    }

    /**
     * @param string $bugDescription
     */
    public function setBugDescription($bugDescription)
    {
        $this->bugDescription = $bugDescription;
    }

    /**
     * @return int
     */
    public function getBugStatus()
    {
        return $this->bugStatus;
    }

    /**
     * @param int $bugStatus
     */
    public function setBugStatus($bugStatus)
    {
        $this->bugStatus = $bugStatus;
    }

    /**
     * @return DateTime
     */
    public function getBugCreated()
    {
        return $this->bugCreated;
    }

    /**
     * @param DateTime $bugCreated
     */
    public function setBugCreated($bugCreated)
    {
        $this->bugCreated = $bugCreated;
    }

    /**
     * @return User
     */
    public function getEngineer()
    {
        return $this->engineer;
    }

    /**
     * @param User $engineer
     */
    public function setEngineer(User $engineer)
    {
        $engineer->assignedToBug($this);
        $this->engineer = $engineer;
    }

    /**
     * @return User
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * @param User $reporter
     */
    public function setReporter(User $reporter)
    {
        $reporter->addReportedBug($this);
        $this->reporter = $reporter;
    }

    /**
     * @param Product $product
     */
    public function assignToProduct(Product $product)
    {
        $product->addOccurredBug($this);
        $this->products[] = $product;
    }

    /**
     * @return ArrayCollection|Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }
}