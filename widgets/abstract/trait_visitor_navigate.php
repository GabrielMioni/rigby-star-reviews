<?php
require_once('trait_append_query_data.php');

/**
 * Contains methods that are shared in the sidebar and paginator_public classes. Both of those classes are children of
 * the abstract class abstract_navigate.
 */
trait trait_visitor_navigate
{
    use trait_append_query_data;

    /**
     * Sets the class variable $result_count in concrete instances of the abstract_navigate class.  Also fulfills that
     * class's declaration requirement for the abstract method abstract_navigate::set_result_count().
     *
     * @return int Returns the count result from the MySQL query.
     */
    protected function set_result_count()
    {
        $rating  = $this->rating;
        $prod_id = $this->product_id;

        $query = "SELECT COUNT(*) FROM star_reviews";
        $pdo = array();

        if ($rating || $prod_id)
        {
            $query .= " WHERE";

            $query .= $this->append_query_data($rating, ' stars = ?', $pdo);
            $query .= ($rating && $prod_id) ? ' AND' : '';
            $query .= $this->append_query_data($prod_id, ' product = ?', $pdo);
        }

        try
        {
            $results = sql_pdo::run($query, $pdo)->fetchColumn();
            return $results;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

}