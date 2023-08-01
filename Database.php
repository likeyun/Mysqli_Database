<?php
/**
 * mySqli数据库操作类
 * 参数绑定防SQL注入
 * 作者：TANKING
 * 时间：2023-08-01
 **/

class Database
{
    private $host;
    private $username;
    private $password;
    private $database;
    private $conn;
    
    // 构造方法
    public function __construct($host, $username, $password, $database)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->connect();
    }
    
    // 连接数据库
    public function connect()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        if ($this->conn->connect_error) {
            die("连接数据库失败：" . $this->conn->connect_error);
        }
    }
    
    // 断开数据库连接
    public function disconnect()
    {
        $this->conn->close();
    }
    
    // Query方法
    public function query($sql, $params = [])
    {
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            throw new Exception("预处理失败：" . $this->conn->error);
        }

        // 绑定参数
        if (!empty($params)) {
            $paramTypes = '';
            $bindParams = [];
            foreach ($params as $param) {
                if (is_int($param)) {
                    $paramTypes .= 'i'; // Integer
                } elseif (is_float($param)) {
                    $paramTypes .= 'd'; // Double
                } else {
                    $paramTypes .= 's'; // String
                }
                $bindParams[] = $param;
            }

            if (!empty($bindParams)) {
                $stmt->bind_param($paramTypes, ...$bindParams);
            }
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            throw new Exception("执行查询失败：" . $stmt->error);
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        return $data;
    }
    
    // 查询一条数据
    public function selectOne($table, $conditions = [], $params = [], $fields = ['*'])
    {
        $limit = 1;
        $result = $this->select($table, $conditions, $params, $limit, $fields);

        if ($result && count($result) > 0) {
            return $result[0];
        }

        return null;
    }
    
    // 查询所有数据
    public function selectAll($table, $conditions = [], $params = [], $fields = ['*'])
    {
        return $this->select($table, $conditions, $params, null, $fields);
    }
    
    // 高级查询
    public function select($table, $conditions = [], $params = [], $limit = null, $fields = ['*'])
    {
        $fields = implode(', ', $fields);
        $whereClause = '';

        if (!empty($conditions)) {
            $whereClause = ' WHERE ' . implode(' AND ', $conditions);
        }

        $limitClause = '';
        if ($limit !== null) {
            $limitClause = ' LIMIT ' . $limit;
        }

        $sql = "SELECT $fields FROM $table $whereClause $limitClause";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            die("预处理失败：" . $this->conn->error);
        }

        $types = '';
        $paramsToBind = [];

        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i'; // Integer
            } elseif (is_float($param)) {
                $types .= 'd'; // Double
            } else {
                $types .= 's'; // String
            }
            $paramsToBind[] = $param;
        }

        array_unshift($paramsToBind, $types);

        $bindResult = call_user_func_array([$stmt, 'bind_param'], $this->refValues($paramsToBind));
        if ($bindResult === false) {
            die("绑定参数失败：" . $this->conn->error);
        }

        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $result;
    }
    
    // 插入数据
    public function insert($table, $data = [])
    {
        if (empty($data)) {
            die("插入数据失败：数据为空");
        }

        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        $params = array_values($data);

        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            die("预处理失败：" . $this->conn->error);
        }

        $types = '';
        $paramsToBind = [];

        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i'; // Integer
            } elseif (is_float($param)) {
                $types .= 'd'; // Double
            } else {
                $types .= 's'; // String
            }
            $paramsToBind[] = $param;
        }

        array_unshift($paramsToBind, $types);

        $bindResult = call_user_func_array([$stmt, 'bind_param'], $this->refValues($paramsToBind));
        if ($bindResult === false) {
            die("绑定参数失败：" . $this->conn->error);
        }
        
        // 插入结果
        $result = $stmt->execute();
        
        // 断开数据库连接
        $stmt->close();
        
        // 返回结果
        return $result;
    }
    
    // 更新数据
    public function update($table, $data = [], $conditions = [], $params = [])
    {
        if (empty($data)) {
            die("更新数据失败：更新数据为空");
        }

        $updateFields = implode(' = ?, ', array_keys($data)) . ' = ?';
        $whereClause = '';

        if (!empty($conditions)) {
            $whereClause = ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql = "UPDATE $table SET $updateFields $whereClause";
        $updateParams = array_merge(array_values($data), $params);

        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            die("预处理失败：" . $this->conn->error);
        }

        $types = '';
        $paramsToBind = [];

        foreach ($updateParams as $param) {
            if (is_int($param)) {
                $types .= 'i'; // Integer
            } elseif (is_float($param)) {
                $types .= 'd'; // Double
            } else {
                $types .= 's'; // String
            }
            $paramsToBind[] = $param;
        }

        array_unshift($paramsToBind, $types);

        $bindResult = call_user_func_array([$stmt, 'bind_param'], $this->refValues($paramsToBind));
        if ($bindResult === false) {
            die("绑定参数失败：" . $this->conn->error);
        }

        $result = $stmt->execute();

        $stmt->close();

        return $result;
    }
    
    // 删除数据
    public function delete($table, $conditions = [], $params = [])
    {
        if (empty($conditions)) {
            die("删除数据失败：删除条件为空");
        }

        $whereClause = ' WHERE ' . implode(' AND ', $conditions);
        $sql = "DELETE FROM $table $whereClause";

        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            die("预处理查询失败：" . $this->conn->error);
        }

        $types = '';
        $paramsToBind = [];

        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i'; // Integer
            } elseif (is_float($param)) {
                $types .= 'd'; // Double
            } else {
                $types .= 's'; // String
            }
            $paramsToBind[] = $param;
        }

        array_unshift($paramsToBind, $types);

        $bindResult = call_user_func_array([$stmt, 'bind_param'], $this->refValues($paramsToBind));
        if ($bindResult === false) {
            die("绑定参数失败：" . $this->conn->error);
        }

        $result = $stmt->execute();

        $stmt->close();

        return $result;
    }
    
    // 执行原生语句
    public function querySQL($sql)
    {
        $result = $this->conn->query($sql);

        if ($result === false) {
            die("执行原生失败：" . $this->conn->error);
        }

        return $result;
    }
    
    // 数据绑定
    private function refValues($arr)
    {
        if (strnatcmp(phpversion(), '5.3') >= 0) // Reference is required for PHP 5.3+
        {
            $refs = array();
            foreach ($arr as $key => $value) {
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }
}

?>