# Getting Started with Doctrine 2 - Part I

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
- 一个产品可以查看该产品下的所有 Bug.
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

### 数据表 - Bug 与产品的关系: relation_bug_product

| 列名 | 说明 |
| --- | --- |
| relation_bug_id | Bug 编号 |
| relation_product_id | 产品编号 |

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

- Product 类定义了2个属性, 对于实体类, 这里我们强调一下. 所有的属性不能设置为 public, 避免被直接访问, 按照 OOP 的设计原则, 通过接口来访问和设置属性更安全.
- 对于 Product 来说, 暂且作为一个数据库表来理解的话, 一个表通常会有一个主键 ID, 对于这种 ID 属性, 特别是由数据库自动生成的, 我们通常不留对外开放的设置或修改接口.

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

- `Entity`: 这个指令是定义在类声明的注释中的. 声明这个类是一个实体.
- `Table`: 这个指令也是定义在类声明的注释中的. 声明这个类在数据库中的表名, 这个指令是带属性的. 属性都定义在一对括号中. 表名由属性`name`来指定, 表名是可以任意定义的, 不需要和实体保持一样的名字, 通常不指定就是默认的实体名, 建议手动指定, 我们这里定义这个表名为: `products`.
- `Id`: 这个指令是定义在类属性的注释中的. 用来说明这个是一个 ID 属性, 也就是主键属性.
- `GeneratedValue`: 这个指令通常是伴随着`Id`指令出现的. 简单的理解就是使用数据库自身的自增值来赋值给主键用. 或者说告诉数据库这个主键是一个 AUTO INCREMENT 或 Sequences 属性.
- `Column`: 这个指令定义在类属性的注释中的, 是对数据表的字段的定义了, 和之前的`Table`指令一样, 带有子属性, 相关的属性一般为: (name, type, length, precision, scale, unique, nullable, options, columnDefinition). 这里特别要说明一下. 为了代码的可读性和便于维护性. 建议对 name 子属性进行明确的定义. 虽然是使用类属性名来作为默认值, 但是在对后面的关系定义会理解的比较麻烦.

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

### 插入数据

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

### 查询所有数据

我们再写一个脚本来进行数据的读取操作. 之前我们通过实体类对数据进行了持久化(也就是存储). 那么我们要读取这些数据也不需要通过去编写 SQL 进行读取. 通过 `EntityManager` 可以很方便的把我们想要的数据读取出来. 

我们来创建一个读取数据脚本: list_product.php

```php
<?php
/**
 * list_product.php
 *
 * Usage: php list_product.php
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

// 获取实体工厂仓库
$repositoryProduct = $entityManager->getRepository(Product::class);

// 提前所有的实体
$products = $repositoryProduct->findAll();

foreach ($products as $product) {
    echo sprintf("Product ID: %d Name: %s" . PHP_EOL,  $product->getProductID(), $product->getProductName());
}
```

上面的代码中. 我们通过 `EntityManager` 对象的工厂方法 `getRepository` 可以获取各种我们需要的实体仓库. 然后进一步的通过 `findAll` 等相关的读取接口可以方便的得到我们想要的各种数据. 当然数据是被实例化到一个我们之前定义的实体类中的. 这样完全透明了对数据的存储读取操作.

我们通过简单终端命令来执行这个脚本来验证一下:

```shell
$ php list_product.php
```

### 查询单个数据

我们通过 list_product.php 的一小段代码实现了所有的数据读取. 我们再写一个更便捷的代码来实现对单个数据的读取.

我们编写显示单个数据的代码: show_product.php

```php
<?php
/**
 * show_product.php
 *
 * Usage: php show_product.php <id>
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

$productID = $argv[1]; //要读取的产品编号
$product = $entityManager->find(Product::class, $productID); // 提取产品数据实例

if(!$product instanceof Product) { //结果检查
    echo 'No product found.' . PHP_EOL;
    exit(1);
}

echo sprintf("Product ID: %d Name: %s" . PHP_EOL,  $product->getProductID(), $product->getProductName());
```

在这段代码中, 我们通过 `find` 方法更加快捷的提取指定的实体数据. `EntityManager` 提供了相当多的开放 api, 这个我们在后面的教程中相继进行介绍. 这里只做简单说明.

我们也通过终端来验证一下代码的可行性.

```shell
$ php show_product.php 1
```

### 更新数据

在之前的部分我们有介绍了插入数据, 查询数据. 我们也简单介绍一下更新数据是如何操作的. 我们来更新一条之前已经插入好的数据. 

我们用 update_product.php 小脚本来展示如何更新数据.

```php
<?php
/**
 * update_product.php
 *
 * Usage: php update_product.php <id> <name>
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

$productID = $argv[1];
$newName = $argv[2];

$product = $entityManager->find(Product::class, $productID);

if (!$product instanceof Product) {
    echo "Product ID: $productID does not exist." . PHP_EOL;
    exit(1);
}

$product->setProductName($newName);

$entityManager->flush();

echo "Product name updated to: " . $newName . PHP_EOL;
```

代码很简单. 显示根据 ID 查找数据映射到实体里, 然后调用实体类的接口修改相关的属性, 然后进行保存操作. 这个过程很简单. 这种修改对象属性从而达到修改数据的模式就是 ORM 的一个基本特征. 当然 ORM 提供的不仅仅是这些. 但是这个思维模式很重要. 不要总想着传统的用 SQL 读取出数据, 然后使用 UPDATE 的 SQL 语句进行操作.

我们测试修改 ID 为1的产品名为新的产品名: "Product-New-Name"

```shell
$ php update_product.php 1 Product-New-Name
```

### 删除数据

删除数据这里我们先不讲了, 可以自己尝试一下. 和更新数据是一个模式, 先需要读取到一个数据实体, 如果是真正的数据实体, 那么只要进行一个 `remove` 操作即可.


## 项目实战 - 05:创建其他的相关实体

针对 ORM 的实体定义和使用, 我们在上一章节进行了比较详细的讲解. 接下来我们把 Bug 追踪系统其他需要用到的实体也一并进行定义. 

除了我们之前用到的 Product 实体, 我们再创建一个 User 实体.

```php
<?php
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
}
```

另外, 还有一个 Bug 实体, 我们也快速创建好.

```php
<?php
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
}
```

我们已经创建好Product, User, Bug 三个实体定义. 最基础的工作已经完成, 那么接下来我们发现缺少了点东西. 对的. 我们之前对这个系统的详细的分析过程中. 他们三者之间是有多种关系的.

我们先从 User 实体来改进. 从最初的系统分析我们知道: 

- 一个用户可以是 Bug 的报告者, 也可能是被指派修复 Bug 的工程师. 
- 并且从用户的角度来看, 一个用户是可以知道自己名下所有报告的 bug 信息和所有指派给自己的 bug 信息. 

所以现在定义的 User 实体类还不能完全包含上面的信息. 我们需要再给这个实体类增加一些属性.

```php
<?php

use Doctrine\Common\Collections\ArrayCollection;

class User
{
    // ... 先前的代码

    /**
     * 我报告的 Bug
     *
     * @var ArrayCollection
     */
    protected $reportedBugs;

    /**
     * 指派给我的 Bug
     *
     * @var ArrayCollection
     */
    protected $assignedBugs;

    public function __construct()
    {
        $this->reportedBugs = new ArrayCollection();
        $this->assignedBugs = new ArrayCollection();
    }
}
```

同样的之前的系统分析中我们注意到一个 Bug 会出现在不同的产品上. 我们再对 Bug 实体的定义做一下修改:

```php
<?php
use Doctrine\Common\Collections\ArrayCollection;

class Bug
{
    // ... 先前的代码

    /**
     * 出现Bug的产品集合
     *
     * @var ArrayCollection
     */
    protected $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }
}
```

接下来我们再进一步的把实体间的关系也引入进来. 首先我们修改一下 User 实体. 添加一下用户的 Bug 报告和被指派的接口.

```php
<?php

use Doctrine\Common\Collections\ArrayCollection;

class User
{
    // ... 之前的代码

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
     * 接收一个指派给我的 Bug
     *
     * @param Bug $bug
     */
    public function assignedToBug(Bug $bug)
    {
        $this->assignedBugs[] = $bug;
    }
}
```

现在把 Bug 和用户的关系操作也添加进来, 在一个 bug 产生的时候, 我们需要指定报告人和处理人.

```php
<?php
use Doctrine\Common\Collections\ArrayCollection;

class Bug
{
    // ... 先前的代码
    
    /**
     * 处理 Bug 的工程师
     *
     * @var User
     */
    protected $engineer;

    /**
     * 报告 Bug 的用户
     *
     * @var User
     */
    protected $reporter;

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
}
```

到这里. Bug 与 User 的操作我们已经定义明确. 我们还需要一个操作定义那就是 Bug 与 Product. 我们也按上面的模式快速的来进行定义(我们在这些关系操作这来讲解的不是非常啰嗦, 因为关系与操作在我们做系统分析的时候做了很详细的说明了. 如果此处还是不清楚请返回看系统分析部分).

```php
<?php
use Doctrine\Common\Collections\ArrayCollection;

class Bug
{
    // ... 先前的代码
    
    /**
     * @param Product $product
     */
    public function assignToProduct(Product $product)
    {
        $this->products[] = $product;
    }

    /**
     * @return ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }
}
```

到此, 我们基本把相关的关系与操作在实体里进行了定义. 不过这个只是从我们的认知上进行定义. 我们还需使用 ORM 的规则告诉 ORM 这些属性的关系. 对的. 使用我们之前用到的 DocBlock 指令对这些新增的属性进行定义. 让我们整理一下我们新建的 User 和 Bug 实体的属性代码. 先把关系定义给加上.

```php
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
```

接下来我们对原教程进行一下升华. 我们把 Product 与 Bug 的双向关系也定义出来(此部分是官方指南没有的, 这里特别放出是更好的理解多对多的关系在Doctrine 的这个 ORM 中的使用)

```php
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
```

最后, 我们把完整的 Bug 实体定义整理出来. 

```php
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
```

到此. 我们已经把这个 Bug 追踪系统用到的实体及关系已经定义完毕. 我们可以使用 ORM 的 schema-tool 测试看看我们定义出的实体关系是不是我们之前做系统分析出的数据库表结构.

```shell
$ vendor/bin/doctrine orm:schema-tool:drop --force
$ vendor/bin/doctrine orm:schema-tool:update --dump-sql
```

我们贴出这个测试的结果. 结果很符合我们的预期. 和之前我们没使用 ORM 的手动设计的数据库结构是一致的.

```sql
CREATE TABLE bugs (
    bug_id INT AUTO_INCREMENT NOT NULL, 
    bug_assigned_to INT DEFAULT NULL, 
    bug_reported_by INT DEFAULT NULL, 
    bug_description VARCHAR(255) NOT NULL, 
    bug_status INT NOT NULL, 
    bug_created DATETIME NOT NULL, 
    INDEX IDX_1E197C98CA12DBB (bug_assigned_to), 
    INDEX IDX_1E197C91100D98E (bug_reported_by), 
    PRIMARY KEY(bug_id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

CREATE TABLE relation_bug_product (
    relation_bug_id INT NOT NULL, 
    relation_product_id INT NOT NULL, 
    INDEX IDX_EDFCBE07ED19B343 (relation_bug_id), 
    INDEX IDX_EDFCBE07AF94BC43 (relation_product_id), 
    PRIMARY KEY(relation_bug_id, relation_product_id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

CREATE TABLE products (
    product_id INT AUTO_INCREMENT NOT NULL, 
    product_name VARCHAR(45) NOT NULL, 
    PRIMARY KEY(product_id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT NOT NULL, 
    user_name VARCHAR(45) NOT NULL, 
    PRIMARY KEY(user_id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

ALTER TABLE bugs ADD CONSTRAINT FK_1E197C98CA12DBB FOREIGN KEY (bug_assigned_to) REFERENCES users (user_id);

ALTER TABLE bugs ADD CONSTRAINT FK_1E197C91100D98E FOREIGN KEY (bug_reported_by) REFERENCES users (user_id);

ALTER TABLE relation_bug_product ADD CONSTRAINT FK_EDFCBE07ED19B343 FOREIGN KEY (relation_bug_id) REFERENCES bugs (bug_id);

ALTER TABLE relation_bug_product ADD CONSTRAINT FK_EDFCBE07AF94BC43 FOREIGN KEY (relation_product_id) REFERENCES products (product_id);

```

OK, 我们执行数据库更新操作. 把定义好的 ORM 实体映射到数据库中去.

```shell
$ vendor/bin/doctrine orm:schema-tool:update --force
```

BTW: 上面的最终实体类定义中我们使用了 DocBlock 的关系指令, 这部分的内容比较复杂我们以后专门讲解. 现阶段只要大概理解意思即可.

## 项目实战 - 06:项目测试

经过上一章节烧脑的实体关系定义, 接下来我们可以轻松一点, 来测试一下我们的工作成果. 

### 添加用户测试

编写一个添加用户的简单脚本: create_user.php

```php
<?php
/**
 * create_user.php
 *
 * Usage: php create_user.php <name>
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

$userName = $argv[1];

$user = new User();
$user->setUserName($userName);

$entityManager->persist($user);
$entityManager->flush();

echo "Created User with ID: " . $user->getUserID() . PHP_EOL;
```

现在我们尝试插入一些产品和用户数据用来测试.

```shell
$ php create_product.php Product-001
$ php create_product.php Product-002
$ php create_product.php Product-003
$ php create_user.php Leo
$ php create_user.php EngineerA
$ php create_user.php EngineerB
$ php create_user.php ReporterA
$ php create_user.php ReporterB
```

同样的. 我们创建一个添加bug数据的脚本: create_bug.php

```php
<?php
/**
 * create_bug.php
 *
 * Usage: php create_bug.php <reporter-id> <engineer-id> <product-ids>
 *
 * @author: Leo
 * @version: 1.0
 */

require_once "bootstrap.php";

$reporterID = $argv[1];
$engineerID = $argv[2];
$productIds = explode(",", $argv[3]);

$reporter = $entityManager->find(User::class, $reporterID);
$engineer = $entityManager->find(User::class, $engineerID);

if (!$reporter instanceof User || !$engineer instanceof User) {
    echo "No reporter and/or engineer found for the given id" . PHP_EOL;
    exit(1);
}

$bug = new Bug();
$bug->setReporter($reporter);
$bug->setEngineer($engineer);
$bug->setBugCreated(new DateTime("now"));
$bug->setBugStatus(1);

$description = sprintf("Something does not work! %s has assigned to %s", $reporter->getUserName(), $engineer->getUserName());

foreach ($productIds as $productId) {
    $product = $entityManager->find(Product::class, (int)$productId);
    if ($product instanceof Product) {
        $bug->assignToProduct($product);
        $description .= ". occurred product: " . $product->getProductName();
    }
}

$bug->setBugDescription($description);

$entityManager->persist($bug);
$entityManager->flush();

echo "Created new Bug with ID: " . $bug->getBugID() . PHP_EOL;
```

同样的. 我们插入几条测试数据进行验证:

```shell
$ php create_bug.php 2 4 1,2
$ php create_bug.php 3 4 3
$ php create_bug.php 1 2 1
```

非常完美, 我们在数据库的 users, products, bugs, relation_bug_product 表中都发现了数据. 并且和我们创建的一一对应.

到这里, 我们基本上对 Doctrine 的 ORM 有了一个大概的了解. 接下来我们将去看看 ORM 对数据读取的操作模式. 鉴于篇幅, 本篇不再继续写下去啦. 太长太长了!



