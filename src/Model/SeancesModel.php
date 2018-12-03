<?php
/**
 * Seances model.
 *
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/seances
 * @author marta(dot)zieba(at)student(dot)uj(dot)edu(dot)pl
 * @copyright Marta Zięba 2015
 */

namespace Model;

use Silex\Application;

/**
 * Class Seances.
 *
 * @category Epi
 * @package Model
 * @use Silex\Application
 */
class SeancesModel
{
    /**
     * Db object.
     *
     * @access protected
     * @var Silex\Provider\DoctrineServiceProvider $db
     */
    protected $db;

    /**
     * Object constructor.
     *
     * @access public
     * @param Silex\Application $app Silex application
     */
    public function __construct(Application $app)
    {
        $this->db = $app['db'];
    }
    /**
     * Gets seances for pagination.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     *
     * @return array Result
     */
    public function getPaginatedSeances($page, $limit)
    {
        $pagesCount = $this->countSeancesPages($limit);
        $page = $this->getCurrentPageNumber($page, $pagesCount);
        $seances = $this->getSeancesPage($page, $limit);
        return array(
            'seances' => $seances,
            'paginator' => array('page' => $page, 'pagesCount' => $pagesCount)
        );
    }
    /**
     * Get all seances on page
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     * @param integer $pagesCount Number of all pages
     * @retun array Result
     */
    public function getSeancesPage($page, $limit)
    {
        $sql = '
            SELECT 
                seances.id, movies.title AS movie_id, locations.name
				AS location_id, locations.city, locations.street,
				locations.number, hall, date, time, seats,
                price
            FROM 
                13_zieba.seances
            LEFT JOIN
                movies
            ON
                seances.movie_id = movies.id
            LEFT JOIN   
                locations
            ON  
                seances.location_id = locations.id
            LIMIT 
                :start, :limit
			';
        $statement = $this->db->prepare($sql);
        $statement->bindValue('start', ($page-1)*$limit, \PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    /**
     * Counts seance pages.
     *
     * @access public
     * @param integer $limit Number of records on single page
     * @return integer Result
     */
    public function countSeancesPages($limit)
    {
        $pagesCount = 0;
        $sql = '
            SELECT COUNT(*) 
                as pages_count 
            FROM 
                seances
		';
        $result = $this->db->fetchAssoc($sql);
        if ($result) {
            $pagesCount = ceil($result['pages_count']/$limit);
        }
        return $pagesCount;
    }

    /**
     * Returns current page number.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $pagesCount Number of all pages
     * @return integer Page number
     *
     */
    public function getCurrentPageNumber($page, $pagesCount)
    {
        return (($page <= 1) || ($page > $pagesCount)) ? 1 : $page;
    }

    /**
     * Gets all seances.
     *
     * @access public
     * @return array Result
     */
    public function getAll()
    {
        try {
            $query = '
                SELECT 
                    seances.id, movies.title AS movie_id, locations.city,
					locations.street, locations.number, locations.name 
					AS location_id, hall, date, time, seats, price 
                FROM 
                    13_zieba.seances
                JOIN
                    movies
                ON
                    seances.movie_id = movies.id
                JOIN    
                    locations
                ON  
                    seances.location_id = locations.id
			';
            return $this->db->fetchAll($query);
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }

    /**
     * Gets single seance data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getSeance($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
                    SELECT 
                        seances.id, movies.title AS movie_id, locations.name
						AS location_id, hall, date, time, seats, price 
                    FROM 
                        13_zieba.seances
                    LEFT JOIN
                        movies
                    ON
                        seances.movie_id = movies.id
                    LEFT JOIN   
                        locations
                    ON  
                        seances.location_id = locations.id
                    WHERE 
                        seances.id = ?
				';
                $result = $this->db->fetchAssoc($query, array((int)$id));
                if (!$result) {
                    return array();
                } else {
                    return $result;
                }
            } else {
                return array();
            }
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
    /**
     * Gets single seance data to display.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getSeanceById($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
                    SELECT 
                        seances.id, movies.title AS movie_id, locations.name
						AS location_id, locations.city, locations.street,
						locations.number, hall, date, time, seats, price 
                    FROM 
                        13_zieba.seances
                    LEFT JOIN
                        movies
                    ON
                        seances.movie_id = movies.id
                    LEFT JOIN   
                        locations
                    ON  
                        seances.location_id = locations.id
                    WHERE 
                        seances.id = ?
				';
                $result = $this->db->fetchAssoc($query, array((int)$id));
                if (!$result) {
                    return array();
                } else {
                    return $result;
                }
            } else {
                return array();
            }
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
    /**
     * Gets seance id.
     *
     * @access public
     * @return array Result
     */
    public function getSeanceId()
    {
        try {
            $query = '
                SELECT 
                    id
                FROM 
                    seances
			';
            return $this->db->fetchAll($query);
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
    
    /**
     * Gets number of seats.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getSeats($id)
    {
        try {
            $query = '
                SELECT
                    seats
                FROM
                    seances
                WHERE
                    id
                LIKE
                    ?
            ';
            $result = $this->db->fetchAssoc($query);
            if ($result) {
                return $result;
            }
            return 0;
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
    
    /**
     * Add single seance data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */

    /**
    * Save seance.
    *
    * @access public
    * @param array $seance Seance data
    * @retun mixed Result
    */
    public function saveSeance($seance)
    {
        if (isset($seance['id'])
            && ($seance['id'] != '')
            && ctype_digit((string)$seance['id'])) {
            // update record
            $id = $seance['id'];
            unset($seance['id']);
            return $this->db->update('seances', $seance, array('id' => $id));
        } else {
            // add new record
            return $this->db->insert('seances', $seance);
        }
    }
    /**
     * Add seance.
     *
     * @access public
     * @param integer $id Record Id
     * @param string $movie Record Movie
     * @param string $location Record Location
     * @param string $hall Record Hall
     * @param string $date Record Date
     * @param string $time Record Time
     * @param string $seats Record Seats
     * @param string $price Record Price
     * @return array Result
     */
    public function addSeance(
        $id,
        $movie_id,
        $location_id,
        $hall,
        $date,
        $time,
        $seats,
        $price
    ) {
        try {
            if (($id != '') && ctype_digit((string)$id) && ($movie != '')
            && ctype_digit((string)$movie) && ($location != '')
            && ctype_digit((string)$location) && ($hall != '')
            && ctype_digit((string)$hall) && ($date != '')
            && ctype_digit((string)$date) && ($time != '')
            && ctype_digit((string)$time) && ($seats != '')
            && ctype_digit((string)$seats) && ($price != '')
            && ctype_digit((string)$price)) {
                $query = '
                    INSERT INTO 
                        `seances` (`id`, `movie_id`, `location_id`, 
                        `hall`, `date`, `time`, `seats`, `price`)
                    VALUES
						('.$id.', '.$movie.', '.$location.', 
						'.$hall.', '.$date.', '.$time.',
						'.$seats.', '.$price.');
				';
                return $this->db->fetchAssoc($query, array((int)$id));
            } else {
                return array();
            }
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
    
    /**
     * Delete single seance data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function deleteSeance($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
                    DELETE FROM 
                        seances 
                    WHERE 
                        id = ?
				';
                return $this->db->delete('seances', array('id' => $id));
            } else {
                return array();
            }
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
    /**
     * Counts current number of ticket
     *
     * @access public
     * @param integer $ticket_seance The number of tickets allocated for the session
     * @param integer $ticket_transaction The number of ticket from transaction
     * @return mixed
     */
    public function countTickets($ticket_seance, $ticket_transaction)
    {
        try {
            $result = $ticket_seance - $ticket_transaction;
            return $result;
        } catch (Exception $e) {
            echo 'Caught exception: ' .  $e->getMessage() . "\n";
        }
    }
    /**
     * Changes numer of seats.
     *
     * @param integer $id Record Id
     * @param integer $tickets Number of tickets
     * @return
     */
    public function changeNumberOfTickets($id, $tickets)
    {
        try {
            $query = '
				UPDATE 
					`seances` 
				SET 
					`seats` = '.$tickets.' 
				WHERE 
					`id`= :id;
			';
            $statement = $this->db->prepare($query);
            $statement->bindValue('id', $id, \PDO::PARAM_INT);
            $statement->execute();
        } catch (Exception $e) {
            echo 'Caught exception: ' .  $e->getMessage() . "\n";
        }
    }
}
