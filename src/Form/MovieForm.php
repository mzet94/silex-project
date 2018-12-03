<?php
/**
 * Movie form.
 *
 * @author Marta Zięba <marta.zieba@student.uj.edu.pl>
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/movies
 * @copyright 2015 Marta Zięba
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Model\DirectorsModel;

/**
 * Class MovieForm.
 *
 * @category Epi
 * @package Form
 * @extends AbstractType
 * @use Symfony\Component\Form\AbstractType
 * @use Symfony\Component\Form\FormBuilderInterface
 * @use Symfony\Component\OptionsResolver\OptionsResolverInterface
 * @use Symfony\Component\Validator\Constraints as Assert
 */
class MovieForm extends AbstractType
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
            'title',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 4))
                )
            )
        )
        ->add(
            'director_id',
            'choice',
            array(
                'choices'=> $this->getDirectorsList($this->app),
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank()
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
        return 'movieForm';
    }
          
    /**
     * Prepares directors for id field.
     *
     * @access public
     *
     * @param Silex\Application $app Silex application
     *
     * @return array
     */
    protected function getDirectorsList($app)
    {
        $directorsModel = new DirectorsModel($app);
        $result = $directorsModel->getAll();
        
        $dict = array();
        foreach ($result as $director) {
            $dict[$director['id']] = $director['surname'];
            $dict[$director['id']] = $director['surname'];
        }
        return $dict;
    }
}
