<?php
/**
 * Transaction form.
 *
 * @author Marta Zięba <marta.zieba@student.uj.edu.pl>
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/transactions
 * @copyright 2015 Marta Zięba
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Model\TransactionsModel;
use Model\SeancesModel;

/**
 * Class TransactionForm.
 *
 * @category Epi
 * @package Form
 * @extends AbstractType
 * @use Symfony\Component\Form\AbstractType
 * @use Symfony\Component\Form\FormBuilderInterface
 * @use Symfony\Component\OptionsResolver\OptionsResolverInterface
 * @use Symfony\Component\Validator\Constraints as Assert
 */
class TransactionForm extends AbstractType
{

   
    /**
     * App.
     *
     * @access protected
     */

    protected $app = null;
  
    /**
     * Object constructor.
     *
     * @access public
     * @param Silex\Application $app Silex application
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    /**
     * Form builder.
     *
     * @access public
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @return FormBuilderInterface
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return  $builder->add(
            'id',
            'hidden',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Type(array('type' => 'digit'))
                )
            )
        )
        ->add(
            'user_id',
            'hidden',
            array(
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank()
                )
            )
        )
        ->add(
            'date',
            'hidden',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Type(array('type' => 'string'))
                )
            )
        )
        ->add(
            'tickets',
            'text',
            array(
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Regex(array(
                        'pattern'     => '/^[1-9][0-9]?$/i',
                        'htmlPattern' => '^[1-9][0-9]+$',
                        'message' => 'Musisz wpisać liczbę. 
						(bez zera i liczb ujemnych)',
                        ))
                )
            )
        )
        ->add(
            'paymentMethod_id',
            'choice',
            array(
                'choices'=> $this->getPaymentMethodsList($this->app),
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank()
                )
            )
        )
        ->add(
            'paymentStatus_id',
            'hidden',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Type(array('type' => 'digit'))
                )
                )
        )
        ->add(
            'collection_id',
            'choice',
            array(
                'choices'=> $this->getCollectionMethodsList($this->app),
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank()
                )
            )
        )
        ->add(
            'transactionDetails_id',
            'hidden',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Type(array('type' => 'digit'))
                )
            )
        )
        ->add(
            'transaction_id',
            'hidden',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Type(array('type' => 'digit'))
                )
            )
        )
        ->add(
            'seance_id',
            'hidden',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Type(array('type' => 'digit'))
                )
            )
        )
        ->add(
            'ticketsPrice',
            'hidden',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Type(array('type' => 'digit'))
                )
            )
        );
    }

    /**
     * Gets form name.
     *
     * @access public
     *
     * @return string
     */
    public function getName()
    {
        return 'transactionForm';
    }

    /**
     * Prepares payment methods for id field.
     *
     * @access public
     *
     * @param Silex\Application $app Silex application
     *
     * @return array
     */
    protected function getPaymentMethodsList($app)
    {
        $paymentMethodsModel = new TransactionsModel($app);
        $result = $paymentMethodsModel->getAllPaymentMethods();
        
        $dict = array();
        foreach ($result as $paymentMethod) {
            $dict[$paymentMethod['id']] = $paymentMethod['name'];
            $dict[$paymentMethod['id']] = $paymentMethod['name'];
        }
        return $dict;
    }
    /**
     * Prepares methods of collection for id field.
     *
     * @access public
     *
     * @param Silex\Application $app Silex application
     *
     * @return array
     */
    protected function getCollectionMethodsList($app)
    {
        $collectionModel = new TransactionsModel($app);
        $result = $collectionModel->getAllCollectionMethods();
        
        $dict = array();
        foreach ($result as $collection) {
            $dict[$collection['id']] = $collection['name'];
            $dict[$collection['id']] = $collection['name'];
        }
        return $dict;
    }
}
