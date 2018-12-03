<?php
/**
 * Transactions model.
 *
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/transactions
 * @author marta(dot)zieba(at)student(dot)uj(dot)edu(dot)pl
 * @copyright Marta Zięba 2015
 */

namespace Model;

use Silex\Application;

/**
 * Class Transactions.
 *
 * @category Epi
 * @package Model
 * @use Silex\Application
 */
class TransactionsModel
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
     * Gets transactions for pagination.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     *
     * @return array Result
     */
    public function getPaginatedTransactions($page, $limit)
    {
        $pagesCount = $this->countTransactionsPages($limit);
        $page = $this->getCurrentPageNumber($page, $pagesCount);
        $transactions = $this->getTransactionsPage($page, $limit);
        return array(
            'transactions' => $transactions,
            'paginator' => array('page' => $page, 'pagesCount' => $pagesCount)
        );
    }
    /**
     * Gets transactions for pagination.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     * @param integer $id Record User id
     *
     * @return array Result
     */
    public function getPaginatedOrders($page, $limit, $id)
    {
        $pagesCount = $this->countOrdersPages($limit, $id);
        $page = $this->getCurrentPageNumber($page, $pagesCount);
        $transactions = $this->getOrdersPage($page, $limit, $id);
        return array(
            'transactions' => $transactions,
            'paginator' => array('page' => $page, 'pagesCount' => $pagesCount)
        );
    }
    
    /**
     * Get all transactions on page
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     * @param integer $pagesCount Number of all pages
     * @retun array Result
     */
    public function getTransactionsPage($page, $limit)
    {
        $sql = '
            SELECT
                transactions.id, users.login, date, tickets, paymentMethods.name,
                paymentStatuses.name, collections.name
            FROM 
                transactions 
            LEFT JOIN
                paymentMethods
            ON
                transactions.paymentMethod_id = paymentMethods.id
            LEFT JOIN
                paymentStatuses
            ON
                transactions.paymentStatus_id = paymentStatuses.id
            LEFT JOIN
                collections
            ON  
                transactions.collection_id = collections.id
            LEFT JOIN
                users
            ON
                transactions.user_id = users.id
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
     * Get all orders on page by user
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     * @param integer $id Record User id
     *
     * @return array Result
     */

    public function getOrdersPage($page, $limit, $id)
    {
        $query = '
			SELECT 
				transactions.id, transactions.date, movies.title, transactionDetails.ticketsPrice
			FROM 
				transactions 
			JOIN 
				transactionDetails
			ON 
				transactions.id = transactionDetails.transaction_id
			JOIN
				seances
			ON
				seances.id = transactionDetails.seance_id
			JOIN
				movies
			ON
				movies.id = seances.movie_id
			WHERE 
				user_id = '.$id.';
			LIMIT 
				:start, :limit';
        $statement = $this->db->prepare($query);
        $statement->bindValue('start', ($page-1)*$limit, \PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    /**
     * Counts transaction pages.
     *
     * @access public
     * @param integer $limit Number of records on single page
     * @return integer Result
     */
    public function countTransactionsPages($limit)
    {
        $pagesCount = 0;
        $sql = 'SELECT COUNT(*) as pages_count FROM transactions';
        $result = $this->db->fetchAssoc($sql);
        if ($result) {
            $pagesCount = ceil($result['pages_count']/$limit);
        }
        return $pagesCount;
    }
    
    /**
     * Counts orders pages.
     *
     * @access public
     * @param integer $limit Number of records on single page
     * @param integer $id Record User id
     * @return integer Result
     */
    public function countOrdersPages($limit, $id)
    {
        $pagesCount = 0;
        $query = '
			SELECT COUNT(*) 
				as pages_count 
			FROM 
				transactions 
			WHERE 
				user_id = '.$id.'';
        $result = $this->db->fetchAssoc($query);
        if ($result) {
            $pagesCount =  ceil($result['pages_count']/$limit);
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
     * Gets all transactions.
     *
     * @access public
     * @return array Result
     */
    public function getAll()
    {
        try {
            $query = '
                SELECT
                    id, users.login, date, tickets, paymentMethod_id,
                    paymentStatus_id, collection_id
                FROM 
                    transactions 
			';
            return $this->db->fetchAll($query);
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }

    /**
     * Gets single transaction data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getTransaction($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
                    SELECT
                        transactions.id, users.login, transactions.date, tickets,
						paymentMethods.name AS paymentMethod_id,
                        paymentStatuses.name AS paymentStatus_id,
						collections.name AS collection_id, movies.title, seances.date 
						AS seance_date,	transactionDetails.ticketsPrice
                    FROM 
                        transactions 
                    LEFT JOIN
                        paymentMethods
                    ON
                        transactions.paymentMethod_id = paymentMethods.id
                    LEFT JOIN
                        paymentStatuses
                    ON
                        transactions.paymentStatus_id = paymentStatuses.id
                    LEFT JOIN
                        collections
                    ON  
                        transactions.collection_id = collections.id
                    LEFT JOIN
                        users
                    ON
                        transactions.user_id = users.id
					LEFT JOIN
						transactionDetails
					ON
						transactions.id = transactionDetails.transaction_id
					LEFT JOIN
						seances
					ON
						transactionDetails.seance_id = seances.id
					LEFT JOIN
						movies
					ON
						seances.movie_id = movies.id
                    WHERE 
                        transactions.id = ?
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
     * Gets single transaction data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getTickets($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
                    SELECT
                        tickets
                    FROM 
                        transactions 
                    WHERE 
                        id= ?
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
     * Add single transaction data.
     *
     * @access public
     * @param integer $id Record Id
     * @param string $user_id Record User
     * @param date $date Record Date
     * @param integer $tickets Record Tickets
     * @param string $paymentMethod_id Record Payment Method
     * @param string $paymentStatus_id Record Payment Status
     * @param string $collection_id Record Collection
     * @return array Result
     */

   /**
    * Save transaction.
    *
    * @access public
    * @param array $transaction Transaction data
    * @retun mixed Result
    */
    public function saveTransaction($transaction)
    {
        if (isset($transaction['id'])
            && ($transaction['id'] != '')
            && ctype_digit((string)$transaction['id'])) {
            // update record
            $id = $transaction['id'];
            unset($transaction['id']);
            return $this->db->update('transactions', $transaction, array('id' => $id));
        } else {
            // add new record
            $this->db->insert('transactions', $transaction);
            return $this->db->lastInsertId();
        }
    }
    
    /**
     * Add transaction.
     *
     * @access public
     * @param integer $id Record Id
     * @param string $user_id Record User
     * @param date $date Record Date
     * @param integer $tickets Record Tickets
     * @param string $paymentMethod_id Record Payment Method
     * @param string $paymentStatus_id Record Payment Status
     * @param string $collection_id Record Collection
     * @return array Result
     */
    public function addTransaction($id, $user, $date, $tickets, $paymentMethod, $paymentStatus, $collection)
    {
        try {
            if (($id != '') && ctype_digit((string)$id) && ($user_id != '')
                && ctype_digit((string)$user_id) && ($date != '')
                && ctype_digit((string)$date) && ($tickets != '')
                && ctype_digit((string)$tickets) && ($paymentMethod != '')
                && ctype_digit((string)$paymentMethod) && ($paymentStatus != '')
                && ctype_digit((string)$paymentStatus) && ($collection != '')
                && ctype_digit((string)$collection)) {
                $query = '
                  INSERT INTO 
                    `transactions` (`id`, `user_id`, `date`, `tickets`,
                    `paymentMethod_id`, `paymentStatus_id`, `collection_id`) 
                  VALUES 
				    ('.$id.', '.$user.', '.$date.', '.$tickets.',
					'.$paymentMethod.', '.$paymentMethod.', '.$collection.');
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
     * Save transaction details.
     *
     * @access public
     * @param array $transactionDetails Transaction Details data
     * @retun mixed Result
     */
    public function saveTransactionDetails($transactionDetails)
    {
        if (isset($transactionDetails['id']) && ($transactionDetails['id'] != '')
            && ctype_digit((string)$transactionDetails['id'])) {
            // update record
            $id = $transactionDetails['id'];
            unset($transactionDetails['id']);
            return $this->db->update(
                'transactionDetails',
                $transactionDetails,
                array('id' => $id)
            );
        } else {
            // add new record
            return $this->db->insert('transactionDetails', $transactionDetails);
        }
    }
    /**
     * Add details of transaction.
     *
     * @access public
     * @param integer $transactionDetails_id Record Id
     * @param string $transaction_id Record Transaction Id
     * @param date $seance_id Record Seance Id
     * @param integer $ticketsPrice Record Tickets Price
     * @return array Result
     */
    public function addTransactionDetails(
        $transactionDetails_id,
        $transaction_id,
        $seance_id,
        $ticketPrice
    ) {
        try {
            if (($transactionDetails_id != '')
            && ctype_digit((string)$transactionDetails_id)
            && ($transaction_id != '') && ctype_digit((string)$transaction_id)
            && ($seance_id != '') && ctype_digit((string)$seance_id)
            && ($ticketPrice != '')
            && ctype_digit((string)$ticketPrice)) {
                $query = '
                  INSERT INTO 
                    `transactionDetails` (`id`, `transaction_id`, `seance_id`, 
                    `ticketPrice`) 
                  VALUES 
				    ('.$transactionDetails_id.', '.$transaction_id.', '.$seance_id.',
					'.$ticketPrice.');
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
     * Delete single transaction data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function deleteTransaction($id)
    {
        try {
            if (($id != '') && ctype_digit((string)$id)) {
                $query = '
                  DELETE FROM 
                    transactions
                  WHERE 
                    id= ?
				';
                return $this->db->delete('transactions', array('id' => $id));
            } else {
                return array();
            }
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
 
    }
   /**
     * Gets all payment methods.
     *
     * @access public
     * @return array Result
     */
    public function getAllPaymentMethods()
    {
        try {
            $query = '
              SELECT 
                id, name 
              FROM 
                paymentMethods
			';
            return $this->db->fetchAll($query);
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
    /**
     * Gets all methods of collection.
     *
     * @access public
     * @return array Result
     */
    public function getAllCollectionMethods()
    {
        try {
            $query = '
              SELECT 
                id, name 
              FROM 
                collections
			';
            return $this->db->fetchAll($query);
        } catch (Exception $exception) {
            echo 'Caught exception: ' .  $exception->getMessage() . "\n";
        }
    }
    /**
     * Counts cost of ticket
     *
     * @access public
     * @param integer $ticket Record Tickets
     * @param integer $ticketPrice Record Ticket Price
     * @return mixed
     */
    public function countPrice($tickets, $ticketPrice)
    {
        try {
            $result = $tickets * $ticketPrice;
            return $result;
        } catch (Exception $e) {
            echo 'Caught exception: ' .  $e->getMessage() . "\n";
        }
    }
}
