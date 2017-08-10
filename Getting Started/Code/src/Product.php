<?php
use Doctrine\Common\Collections\ArrayCollection;

/**
 * src/Product.php
 *
 * @author: Leo
 * @version: 1.0
 *
 * @Entity
 * @Table(name="products")
 */
class Product
{
    /**
     * @var integer
     * @Id
     * @GeneratedValue
     * @Column(type="integer", name="product_id")
     */
    protected $productID;

    /**
     * @var string
     * @Column(type="string", name="product_name", length=45, nullable=false)
     */
    protected $productName;

    /**
     * 产品所出现的 Bug 集合
     *
     * @var Bug[] An ArrayCollection of Bug objects
     * @ManyToMany(targetEntity="Bug", mappedBy="bugs")
     * @JoinTable(
     *     name="relation_bug_product",
     *     joinColumns={@JoinColumn(name="relation_product_id", referencedColumnName="product_id")},
     *     inverseJoinColumns={@JoinColumn(name="relation_bug_id", referencedColumnName="bug_id")}
     * )
     */
    protected $occurredBugs;


    public function __construct()
    {
        $this->occurredBugs = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getProductID()
    {
        return $this->productID;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;
    }

    /**
     * @param Bug $bug
     */
    public function addOccurredBug(Bug $bug)
    {
        $this->occurredBugs[] = $bug;
    }

    /**
     * @return Bug[]|ArrayCollection
     */
    public function getOccurredBugs()
    {
        return $this->occurredBugs;
    }
}