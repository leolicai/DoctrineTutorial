# Getting Started with Doctrine 2

*先声明一下: 本文翻译自官方的使用向导文档, 原文地址是 [http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/getting-started.html](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/getting-started.html). 相比较官方文档, 个人未完全按照原文翻译, 并且加入了相当多的个人理解. 谨代表个人观点. 更多请查看官方指导说明*

本章将介绍 Doctrine ORM 的如下几个方面的知识.
- 如何安装和配置 Doctrine.
- 建立 PHP 的对象与数据库表的映射.
- 从 PHP 的对象构建数据库结构.
- 使用 `EntityManager` 进行数据库中的 **增删改查** 操作.

# 预备知识
本章主要面向未使用过 Doctrine ORM 的同学. 因此在开始之前, 需要你的PHP开发环境已经建立完成. 并确保下面的几项务必已经可用.
- PHP (安装当前的最新稳定版)
- Composer 包管理器 ([安装方法](https://getcomposer.org/doc/00-intro.md))
本教程的开源代码托管在 Github 上. [可以从这里下载](https://github.com/doctrine/doctrine2-orm-tutorial)

> 注意, 本教程基于 Doctrine 2.4 以上版本可用.

# 相关概念-什么是 Doctrine?
Doctrine 2 是 PHP 编程语言中的一个类库. 主要是用来处理数据持久存储方面的工作, 也就是常说的一种 ORM (Object-Relational Mapper). 基于 PHP5.4+ 以上版本. 
使用 ORM 可以让工程师在数据存储管理方面可以节省不少时间. 可以让工程师投入更多的精力专注程序逻辑设计和开发. 这个也是 ORM 存在的核心意义.

# 相关概念-什么是 Entities?
Entity 简单的说就是实体, 是 PHP 的一个对象. 既然是 PHP 的一个对象那么这个对象是有属性和方法的. 这个和其他的 PHP 对象稍微的区别是不要实现 clone 和 wakeup 方法. 也不能包含任何 final 的定义或者声明. 

那么一个 Entity 的属性简单的来看其实就是一个数据表的所有列. 这样来理解其实一个实体其实就是把数据表中的一行数据读取出来, 可能在没用 ORM 之前直接是转成数组或者 stdClass 什么的数据类型. 在使用 Doctrine ORM 之后, 这行数据会转成一个实体, 这个实体的属性和接口都是对这行数据进行操作.

# 实例操作: Bug 追踪系统
我们使用一个简单系统来展示如何在项目中使用 ORM 带来的便利. 我们这里借用一下 Zend Framework 这个 PHP 的 MVC 开发框架的 DB 处理组件文档中使用的例子: [Bug Tracker](http://framework.zend.com/manual/1.12/en/zend.db.adapter.html). 我们先大概的了解一下这个系统的需求.

- 一个 Bug 的定义包含了 说明, 创建时间, 状态, 报告者, 和处理这个 bug 的工程师.
- 一个 Bug 可以出现在不同的产品中.
- 一个产品必须有一个名字.
- Bug 的报告者和处理 Bug 的工程师都是一个群体. 都是一个用户系统中的用户.
- 用户系统中的所有用户都可以是 Bug 的报告者.
- 被指定处理 bug 的工程师能够关闭一个已经处理好的 Bug.
- 任意一个用户都可以查看所有该用户报告的 bug 和所有指派给该用户的 Bug.
- Bug 清单可以被分页显示.

以上就是一个 Bug Tracker 一个基本的需求. 做过系统设计的同学看一眼会觉得非常简单. 通常这个时候首先想到的就是创建一个数据库, 添加数据表, 然后写 SQL, 然后编写 PHP 代码进行数据的增删改查. 如果需求有变更. 可能还需要重复上面的步骤.

## Bug Tracker 的标准数据库设计
我们先用标准的数据库中的表来实现上面的系统需求. 通过上面的需求列表我们分析得出, 我们有三种不同的数据对象, 分别是 bug, 用户, 和产品. 并且这三个对象之间还有一定的联系:

- 一个 bug 可以存在多个产品中. 那么从这里来看 bug 与 产品是 一对多(1:N)的关系.
- 一个产品可能有多个 Bug 存在. 那么从这里看来 产品 与 bug 之间也是 一对多 (1:N) 的关系.
- 所以我们可以确定 bug 和 产品之间的关系是 多对多(N:N) 的关系
- 一个 bug 只能是由一个用户提出, 一个用户可以提出多个 Bug. 那么 Bug 与 用户的关系只能是 多对一(N:1) 的关系.
- 一个 bug 只能指派给一个工程师来出来, 那么一个工程师可以处理多个 BUG, 那么 Bug 与被指派的工程师来说也是 多对一(N:1) 的关系.
- 所以整体来看, bug 与用户的关系是 多对一(N:1) 的关系
- 用户与产品, 我们分析需求, 这两者并无关系. 所以先不用考虑.

我们分析完对象之前的相互关系, 那么我们接下来就很好创建数据表来存储整体系统的数据了. 通过上面的分析, 我们知道只需要4张数据表就可以完全的满足它们之间的相互关系.

### 数据表 - 用户: users
| 列名 | 说明 |
| --- | --- 
| user_id | 用户编号 |
| user_name | 用户名称 |

### 数据表 - 产品: products
| 列名 | 说明 |
| --- | --- |
| product_id | 产品编号 |
| product_name | 产品名称 |

### 数据表 - Bug: bugs
| 列名 | 说明 |
| --- | --- |
| bug_id | Bug 编号 |
| bug_reported_by | Bug 的报告者, Users:user_id 的外键 |
| bug_assigned_to | Bug 的处理者, Users:user_id 的外键 |
| bug_description | Bug 的描述信息 |
| bug_status | Bug 的状态 |
| bug_created | Bug 的创建时间 |

### 数据表 - Bug 与产品的关系: bug_product
| 列名 | 说明 |
| --- | --- |
| id | 流水号 |
| bug_id | Bug 编号 |
| product_id | 产品编号 |

> 到此为止, 我们已经为该系统创建了完整的数据库结构. 接下的事就是编写长长的代码实现这些数据的存储和变更.

这里我们不再用传统的方法去写这个系统. 没有什么意义. 这里我特意的把标准的数据表结构设计出来一来是先理解整个数据系统的设计原理和结构. 二来是接下来使用 ORM 的时候对比来学习会更加容易理解 ORM 的使用.

# ORM 项目实战
接下来我们创建一个具体可实施的项目, 一步一步的来探究 ORM 的具体使用和效果. 项目文件可以存放在任意文件夹下面. 我们接下的命令执行都是在此目录下进行执行.

## ORM 项目实战 - 01:初始化
由于我们使用 PHP 的 composer 包管理器来搭建项目. 所有初始化最开始的事我们需要创建一个简单的 composer.json 配置文件放到项目的根目录. 我们使用最简单模式开始. 配置文件内容如下:

```json
{
    "require": {
        "doctrine/orm": "^2.4"
    },
    "autoload": {
        "psr-0": {
            "": "src/"
        }
    }
}
```

接下来. 我们执行包管理器命令让项目需要的包自动部署好.

```shell
$ composer install
```
Composer 会把相关的包都下载好保存在 vendor 目录下. 我们不用关心具体下载了哪些包.

## 项目实战 - 02:生成实体管理器
Doctrine 这个 ORM 的具体接口都是通过 `EntityManager`(实体管理器) 来操作. 所以我们需要先生成这个 `EntityManager`. 我们使用下面的代码来创建 `EntityManager`, 具体的代码分析我们在下面会详细的讲解.

```php
<?php
/**
 * bootstrap.php
 *
 * ORM 启动配置
 *
 * @author: Leo
 * @version: 1.0
 */

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

// Autoload 配置
require_once "vendor/autoload.php";

// ORM 配置
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration([__DIR__ . "/src"], $isDevMode);

// 数据库连接配置
$dbConfig = [
	'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'user' => 'root',
    'password' => 'root',
    'port' => 3306,
    'dbname' => 'test',
    'charset' => 'utf8mb4',
    'defaultTableOptions' => [
        'collate' => 'utf8mb4_unicode_ci',
        'charset' => 'utf8mb4',
        'engine' => 'InnoDB',
    ],
];

// 创建实体管理器
$entityManager = EntityManager::create($dbConfig, $config);
```
创建实体管理的代码非常简单. 主要是生成配置文件这里我们需要传入我们的实体定义类保存路径. 这里我们会把实体的定义类都放置在 src 目录下. 我们这里只是作为一个测试学习使用的项目目录布局. 真实的项目源代码组织结构可能要比当前的复杂一些. 不过本质是一样的. 需要告诉实体管理器的配置文件哪里可以找到实体定义类. **src目录我们需要自行手动先创建好.**
另外一点是我们使用 MySQL 作为测试数据库平台. 原版教程使用的是 Sqlite. 实际上关系不大, 使用 MySQL 主要是可以更多的先让大家可以接触一些配置信息. 所以我们需要自己搭建一下 MySQL 服务器环境, 确保通过配置文件的 host, user, password, port 这些信息能够连接数据库, 并且能够有足够的权限针对 dbname 这个数据库进行操作.

## 项目实战 - 03:生成数据库
由于我们使用命令行来操作演示 ORM, 所以 Doctrine 在使用相关的命令行工具的时候需要给这个命令行工具进行相关的配置, 配置的过程非常简单. 只需要简单一行代码就可以完成. 具体的代码如下:

```php
<?php
/**
 * cli-config.php
 *
 * Console 配置
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
```
到此, 我们执行 ORM 的命令的环境准备工作就完成了. 我们可以执行相关的命令看看效果. **所有的命令执行的终端当前目录均为项目根目录**. 接下来不再重复说明啦.

通常我们第一步是进行创建数据表. 使用如下命令执行:

```shell
$ cd project-root-path/
$ vendor/bin/doctrine orm:schema-tool:create
```
通常这个时候我们执行完这个命令会得到下面的提示信息:

```shell
No Metadata Classes to process.
```
这个是因为我们还没有定义任何实体类在 src 目录中. 接下来我们开始定义我们的实体类. 在定义实体类之前我们先了解一下 schema-tool 的另外两个简单命令. 通常我们操作数据库都会涉及到 创建, 删除, 更新这三类操作. 那么对应的 schema-tool 也提供了这三种命令. 创建命令这个我们刚才已经使用过了. 那么删除命令具体的使用方法如下:

```shell
$ vendor/bin/doctrine orm:schema-tool:drop --force
```

更新命令也是类似的:

```shell
$ vendor/bin/doctrine orm:schema-tool:update --force
```
我们接下来在具体的实体操作中再继续使用这些命令.

## 项目实战 - 04:创建产品实体类
我们先创建一个简单的实体, 在这个 Bug 追踪系统中我们有一个很简单实体就是产品(Proudct). 我们之前在配置文件声明了所有的实体类存放在项目根目录下的 src 目录中. 我们接下来的所有实体定义类都会存放在该目录下. 我们先定义一个简单实体类. 

```php
<?php
/**
 * src/Product.php
 *
 * @author: Leo
 * @version: 1.0
 */
class Product
{
    /**
     * @var integer
     */
    protected $productID;

    /**
     * @var string
     */
    protected $productName;


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
}
```
很简单的 PHP 类定义, 我们还是简要的说明一下. 
Product 类定义了2个属性, 对于实体类, 这里我们强调一下. 所有的属性不能设置为 public, 避免被直接访问, 按照 OOP 的设计原则, 通过接口来访问和设置属性更安全.
对于 Product 来说, 暂且作为一个数据库表来理解的话, 一个表通常会有一个主键 ID, 对于这种 ID 属性, 特别是由数据库自动生成的, 我们通常不留对外开放的设置或修改接口.

接下来我们还要做一些工作来告诉 Doctrine 这个 ORM. 要让 ORM 更好的理解这个Product实体, 以便实现数据库持久化. 这里使用了 DocBlock 声明指令. 这个是一个单独的技术章节. 我们在以后的教程中具体的介绍 DocBlock 在类注释中的使用细节. 暂时不了解也没有关系. 我们先使用几个简单的指令, 然后再具体的说明一下. 针对我们刚才编写的 src/Product.php 文件, 我们添加一些 DocBlock 指令.

```php
<?php
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
    
    // .. 其他代码与之前的保持一致
}
```
现在我们具体的说明一下加了 DocBlock 声明的代码, DocBlock 的指令或者声明都是保存在注释中. 并且由 `@` 开始.
`Entity`: 这个指令是定义在类声明的注释中的. 声明这个类是一个实体.
`Table`: 这个指令也是定义在类声明的注释中的. 声明这个类在数据库中的表名, 这个指令是带属性的. 属性都定义在一对括号中. 表名由属性`name`来指定, 表名是可以任意定义的, 不需要和实体保持一样的名字, 通常不指定就是默认的实体名, 建议手动指定, 我们这里定义这个表名为: `products`.
`Id`: 这个指令是定义在类属性的注释中的. 用来说明这个是一个 ID 属性, 也就是主键属性.
`GeneratedValue`: 这个指令通常是伴随着`Id`指令出现的. 简单的理解就是使用数据库自身的自增值来赋值给主键用. 或者说告诉数据库这个主键是一个 AUTO INCREMENT 或 Sequences 属性.
`Column`: 这个指令定义在类属性的注释中的, 是对数据表的字段的定义了, 和之前的`Table`指令一样, 带有子属性, 相关的属性一般为: (name, type, length, precision, scale, unique, nullable, options, columnDefinition). 这里特别要说明一下. 为了代码的可读性和便于维护性. 建议对 name 子属性进行明确的定义. 虽然是使用类属性名来作为默认值, 但是在对后面的关系定义会理解的比较麻烦.

现在我们已经完成了一个完整的 ORM 实体定义, 那么我们看看 ORM 怎么反向映射到数据库中. 我们使用下面的命令先把 ORM 解析出的 SQL 打印出来看看.

```shell
$ vendor/bin/doctrine orm:schema-tool:update --force --dump-sql
```
我们再说一下上面的这条命令, 这是一条更新数据库结构的命令. 同时带了2个参数 `--force`, `--dump-sql`. 意思很明显. 就是告诉 ORM 的 schema-tool. 强制执行更新操作. 同时把更新的 SQL 打印出来. 对于上面的这个实体更新, 我们可以看到这样的 SQL 语句被打印到屏幕上:

```shell
CREATE TABLE products (product_id INT AUTO_INCREMENT NOT NULL, product_name VARCHAR(45) NOT NULL, PRIMARY KEY(product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

Updating database schema...
Database schema updated successfully! "1" query was executed
``` 
到此, 我们已经使用 ORM 实现了类定义到数据表的转化过程. 相比不使用 ORM 这个管理工具来说. 我们需要先创建数据数表, 然后再定义一个处理这个数据表的类, 或者更简单的做法是不定义类, 直接写不同的 SQL 语句在 PHP 代码中进行对数据表数据的增删改查. 通常这种原始的, 暴力的, 高效的做法我们是不赞同的. 因为代码不安全, 不利于维护, 不够优雅.

定义好了实体类, 我们需要把这个实体类的利用起来, 不然我们手动创建一个数据表更方便. 不需要定义了一个类还加入奇怪的 DocBlock 指令在注释中.
我们接下来演示如何便捷的对数据表中数据进行操作. 接下来的代码我们已经看不见 SQL 了. 因为我们不再需要去编写 SQL 啦.
我们使用一小段代码实现增加数据的功能. 

```php
<?php
/**
 * create_product.php
 *
 * Usage: php create_product.php <name>
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

// 提取输入的产品名称
$newProductName = $argv[1];

// 创建实体对象并设置属性
$entityProduct = new Product();
$entityProduct->setProductName($newProductName);

// 数据持久化
$entityManager->persist($entityProduct);
$entityManager->flush();

echo 'Created Product With ID: ' . $entityProduct->getProductID() . PHP_EOL;
```
PHP 脚本编写好了我们就可以使用了. 我们在终端执行类似下面的命令. 

```shell
$ php create_product.php Product-Name-001
```
我们可以打开数据库, 可以查看到数据表 products 中多了一条 product_name 为 "Product-Name-001" 的数据. 我们还可以重复执行, 添加多条数据.
我们发现插入一条新数据只要创建一个 Product 对象实例, 然后设置好相关的属性值. 再通过 `EntityManager` 的 `persist()` 和 `flush()` 接口就实现了数据入库的操作. 对的. 这个过程很友好, 简洁, 漂亮. 很 OOP.








