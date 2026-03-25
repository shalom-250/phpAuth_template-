<?php
// Helper Functions
function jsonResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    return json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}

function maskEmail($email) {
    $parts = explode('@', $email);
    if (count($parts) !== 2) return $email;
    
    $username = $parts[0];
    $domain = $parts[1];
    
    $masked_username = substr($username, 0, 3) . str_repeat('*', max(strlen($username) - 6, 3)) . substr($username, -3);
    return $masked_username . '@' . $domain;
}

function maskString($string, $visible_chars = 3) {
    if (strlen($string) <= $visible_chars * 2) {
        return str_repeat('*', strlen($string));
    }
    return substr($string, 0, $visible_chars) . 
           str_repeat('*', max(strlen($string) - ($visible_chars * 2), 3)) . 
           substr($string, -$visible_chars);
}

// Updated executeQuery function to work with PDO
function executeQuery($query, $params = [], $fetchMode = 'ASSOC') {
    global $pdo; // Use the PDO connection from db.php
    
    try {
        // Check if it's a SELECT query
        $isSelect = stripos(trim($query), 'SELECT') === 0;
        
        // Prepare the statement
        $stmt = $pdo->prepare($query);
        
        // Execute with parameters
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        
        if ($isSelect) {
            // For SELECT queries, fetch data
            switch (strtoupper($fetchMode)) {
                case 'ASSOC':
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                case 'COLUMN':
                    $data = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    break;
                case 'ROW':
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    break;
                default:
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return [
                'success' => true,
                'data' => $data,
                'rowCount' => $stmt->rowCount()
            ];
        } else {
            // For INSERT/UPDATE/DELETE queries
            $lastInsertId = $pdo->lastInsertId();
            
            return [
                'success' => true,
                'affectedRows' => $stmt->rowCount(),
                'last_insert_id' => $lastInsertId,
                'message' => 'Query executed successfully'
            ];
        }
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'errorCode' => $e->getCode()
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

?>