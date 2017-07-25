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




