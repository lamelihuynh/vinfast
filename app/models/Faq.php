<?php
/**
 * app/models/Faq.php — FAQ Model
 * Table: faqs
 * Owner: Nhat Linh (Member 2)
 * Used by: FaqController (frontend), FaqAdminController (admin)
 *
 * Columns: id, question, answer, sort_order, is_active, created_at
 */
class Faq {
    
    public static function getAllForAdminDatatable(): array {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            f.*,
            t.name AS topic_name
        FROM faqs f
        INNER JOIN faq_topics t ON t.id = f.topic_id
        ORDER BY
            t.sort_order ASC,
            f.sort_order ASC,
            f.created_at DESC
    ");

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}


    /**
     * Get all FAQ active, sort by sort_order
     */
    public static function all(): array {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT
            f.*,
            t.name AS topic_name,
            t.slug AS topic_slug,
            t.icon_svg
        FROM faqs f
        INNER JOIN faq_topics t ON t.id = f.topic_id
        WHERE
            f.is_active = 1
            AND t.is_active = 1
        ORDER BY
            t.sort_order ASC,
            f.sort_order ASC,
            f.created_at DESC
    ");

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}


    /**
     * Get FAQ by ID
     */
    public static function getById(int $id): ?array{

        global $pdo;
        $stmt = $pdo -> prepare('
            SELECT f.*, t.name AS topic_name
            FROM faqs f
            INNER JOIN faq_topics t ON t.id = f.topic_id
            WHERE f.id = ?
        ');
        $stmt -> execute([$id]); 
        $row = $stmt -> fetch(PDO::FETCH_ASSOC);
        return $row ?: null; 
    }


    /**
     * Get all FAQ (include inactive) for admin with pagination
     */
    public static function getForAdmin(int $page = 1, int $perPage = 10): array {
        global $pdo; 
        $offset = ($page -1) * $perPage; 

        $stmt = $pdo -> prepare('
            SELECT id, question, answer, sort_order, is_active, created_at
            FROM faqs
            ORDER BY sort_order ASC, created_at DESC
            LIMIT ? OFFSET ? 
        ');
        $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt -> fetchAll(PDO::FETCH_ASSOC) ?: [];
    }



    /**
     * Count all FAQ (for pagination)
     */
    public static function countAll(): int{

        global $pdo; 
        $stmt = $pdo -> query('SELECT COUNT(*) FROM faqs'); 
        return (int)$stmt -> fetchColumn();
    }

    /**
     * Create new FAQ 
     */
    public static function create(int $topicId, string $question, string $answer, int $sortOder = 0, bool $isActive = true): bool{        
        global $pdo; 
        $question = trim($question); 
        $answer = trim($answer);

        if ($question === '' || $answer === ''){
            return false; 
        }

        $sql = 
            'INSERT INTO faqs (topic_id, question, answer, sort_order, is_active, created_at)
            VALUES (:topic_id, :q, :a, :sort, :active, NOW())';
        $stmt = $pdo -> prepare($sql);

        $stmt->bindValue(':q', $question, PDO::PARAM_STR);
        $stmt->bindValue(':a', $answer, PDO::PARAM_STR);
        $stmt->bindValue(':sort', $sortOder, PDO::PARAM_INT);
        $stmt->bindValue(':active', $isActive ? 1: 0, PDO::PARAM_INT);
        $stmt->bindValue(':topic_id', $topicId, PDO::PARAM_INT);
        
        return $stmt->execute(); 
    }


    /**
     * Update FAQ 
     */
    public static function update (int $id,  int $topicId, string $question, string $answer, int $sortOder = 0, bool $isActive = true): bool {
        global $pdo; 

        $question = trim($question);
        $answer = trim($answer);
        if ($question === ''|| $answer === ''){
            return false; 
        }

        $sql = 'UPDATE faqs 
                SET topic_id =:topic_id, question = :q, answer = :a, sort_order= :sort, is_active=:active  
                WHERE id =:id'; 

        $stmt = $pdo -> prepare($sql);

        $stmt->bindValue(':q', $question, PDO::PARAM_STR);
        $stmt->bindValue(':a', $answer, PDO::PARAM_STR);
        $stmt->bindValue(':sort', $sortOder, PDO::PARAM_INT);
        $stmt->bindValue(':active', $isActive ? 1: 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':topic_id', $topicId, PDO::PARAM_INT);

        
        return $stmt -> execute();
    }


    /**
     * Delete FAQ 
     */
    public static function delete ($id) : bool{
        global $pdo;
        $sql = 'DELETE FROM faqs WHERE id = ?';
        $stmt = $pdo -> prepare($sql);
        return $stmt->execute([$id]);
    }
}