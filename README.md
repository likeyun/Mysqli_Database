# Mysqli_Database
php_Mysqli_Database数据库操作类

# 说明
这是一个php深度封装的MySQLi数据库操作类，支持插入、删除、查询和更新操作，并且使用数组进行参数传递，结合了预处理语句防止SQL注入。

# 插入数据

```
<?php

// 引入配置文件
require_once 'Db.php';

// 实例化Database类并连接数据库
$db = new Database($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// 插入数据
$insertParams = array(
    'stu_name' => '蔡徐坤',
    'stu_sex' => '男',
    'stu_from' => '广州',
    'stu_grade' => '一年级',
    'stu_age' => 30,
);

// 执行
$insertData = $db->insert('students', $insertParams);

// 执行结果
if($insertData){
    
    echo '插入成功！'; 
}else{
    
    echo '插入失败！'.$insertData;
}

// 关闭连接
$db->disconnect();

?>
```

# 更新数据

```
<?php

// 引入配置文件
require_once 'Db.php';

// 实例化Database类并连接数据库
$db = new Database($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// 被更新的数据
$updateData = array(
    'stu_name' => '吴亦凡666',
    'stu_age' => 35
);

// 绑定参数
$updateCondition = array('id = ?');
$updateParams = array(1);

// 执行
$updateResult = $db->update('students', $updateData, $updateCondition, $updateParams);

// 执行结果
if($updateResult){
    
    echo '更新成功！'; 
}else{
    
    echo '更新失败！'.$updateResult;
}

// 关闭连接
$db->disconnect();

?>

```

# 删除数据

```
<?php

// 引入配置文件
require_once 'Db.php';

// 实例化Database类并连接数据库
$db = new Database($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// 绑定参数
$conditions = array('id = ?');
$params = array(2);

// 执行
$deleteResult = $db->delete('students', $conditions, $params);

if ($deleteResult) {
    
    echo "删除成功！";
} else {
    
    echo "删除失败。";
}

// 关闭连接
$db->disconnect();

?>
```

# 查询一条数据

```
<?php

// 引入配置文件
require_once 'Db.php';

// 实例化Database类并连接数据库
$db = new Database($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// 准备查询的条件和字段
$conditions = array('id = ?');
$params = array(1);
$fields = array('id', 'stu_name', 'stu_age', 'stu_from');

// 执行
$selectedData = $db->selectOne('students', $conditions, $params, $fields);

// 执行结果
if ($selectedData) {
    
    echo "查询到一条数据：<br>";
    echo "ID: " . $selectedData['id'] . "<br>";
    echo "stu_name: " . $selectedData['stu_name'] . "<br>";
    echo "stu_age: " . $selectedData['stu_age'] . "<br>";
    echo "stu_from: " . $selectedData['stu_from'] . "<br>";
} else {
    
    echo "未查询到数据。";
}

// 关闭连接
$db->disconnect();

?>
```

# 查询所有数据

```
<?php

// 引入配置文件
require_once 'Db.php';

// 实例化Database类并连接数据库
$db = new Database($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// 准备查询的条件和字段
$conditions = array('stu_sex = ?');
$params = array('男');
$fields = array('id', 'stu_name', 'stu_age', 'stu_from');

// 执行
$selectedData = $db->selectAll('students', $conditions, $params, $fields);

// 执行结果
if ($selectedData) {
    
    echo "查询到的所有数据：<br>";
    foreach ($selectedData as $data) {
        echo "ID: " . $data['id'] . "<br>";
        echo "stu_name: " . $data['stu_name'] . "<br>";
        echo "stu_age: " . $data['stu_age'] . "<br>";
        echo "stu_from: " . $data['stu_from'] . "<br>";
        echo "<br>";
    }
} else {
    
    echo "未查询到数据。";
}

// 关闭连接
$db->disconnect();

?>
```

# 高级查询

```
<?php

// 引入配置文件
require_once 'Db.php';

// 实例化Database类并连接数据库
$db = new Database($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// 准备查询的条件和字段
$conditions = array('stu_age > ?');
$params = array(25);
$fields = array('id', 'stu_name', 'stu_age', 'stu_from');
$limit = 3; // 查询限制条数
$orderBy = 'id DESC'; // 排序方式

// 执行
$selectedData = $db->select('students', $conditions, $params, $fields, $limit, $orderBy);

// 执行结果
if ($selectedData) {
    
    echo "查询到的数据：<br>";
    foreach ($selectedData as $data) {
        echo "ID: " . $data['id'] . "<br>";
        echo "stu_name: " . $data['stu_name'] . "<br>";
        echo "stu_age: " . $data['stu_age'] . "<br>";
        echo "stu_from: " . $data['stu_from'] . "<br>";
        echo "<br>";
    }
} else {
    
    echo "未查询到数据。";
}

// 关闭连接
$db->disconnect();

?>
```

# 执行原生语句

```
<?php

// 引入配置文件
require_once 'Db.php';

// 实例化Database类并连接数据库
$db = new Database($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// 执行
$sql = "SELECT * FROM students WHERE stu_age > 25";
$result = $db->querySQL($sql);

// 执行结果
if ($result->num_rows > 0) {
    
    echo "查询到的数据：<br>";
    while ($data = $result->fetch_assoc()) {
        echo "ID: " . $data['id'] . "<br>";
        echo "stu_name: " . $data['stu_name'] . "<br>";
        echo "stu_age: " . $data['stu_age'] . "<br>";
        echo "stu_from: " . $data['stu_from'] . "<br>";
        echo "<br>";
    }
} else {
    
    echo "未查询到数据。";
}

// 关闭连接
$db->disconnect();

?>
```

# 作者
TANKING

