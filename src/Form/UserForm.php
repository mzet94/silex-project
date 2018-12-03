<?php
/**
 * User form.
 *
 * @author Marta Zięba
 * @link http://wierzba.wzks.uj.edu.pl/~13_zieba/silex_test/users/add
 * @copyright Marta Zięba 2015
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Model\RolesModel;

/**
 * Class UserForm.
 *
 * @category Epi
 * @package Form
 * @extends AbstractType
 * @use Symfony\Component\Form\AbstractType
 * @use Symfony\Component\Form\FormBuilderInterface
 * @use Symfony\Component\OptionsResolver\OptionsResolverInterface
 * @use Symfony\Component\Validator\Constraints as Assert
 */
class UserForm extends AbstractType
{

     
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
            'login',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 3, 'max' => 16))
                )
            )
        )
        ->add(
            'password',
            'password',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 8, 'max' => 45))
                )
            )
        )
        ->add(
            'role_id',
            'hidden'
        )
        ->add(
            'firstName',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 3, 'max' => 15))
                )
            )
        )
        ->add(
            'surname',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 3, 'max' => 20))
                )
            )
        )
        ->add(
            'phone',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Regex(array(
                                'pattern' => '/^[1-9][0-9]{8}$/',
                                'htmlPattern' => '^[1-9][0-9]{8}$',
                                'message' => 'Type the number.',
                            )),
                    new Assert\Length(array('min' => 9, 'max' => 9))
                )
            )
        )
        ->add(
            'email',
            'email',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Regex(array(
                                'pattern'
                                    => '/^[a-zA-Z0-9._]+@[a-zA-Z]+.[a-z]{2,3}$/',
                                'message' => 'Wpisz poprawny email.'
                            )),
                    new Assert\Length(array('min' => 8, 'max' => 25))
                )
            )
        )
        ->add(
            'address_id',
            'hidden',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Type(array('type' => 'digit'))
                )
            )
        )
        ->add(
            'city',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 3, 'max' => 30))
                )
            )
        )
        ->add(
            'street',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 5, 'max' => 25))
                )
            )
        )
        ->add(
            'number',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Regex(array(
                                'pattern' => '/^[0-9]+$/',
                                'htmlPattern' => '^[0-9]+$',
                                'message' => 'Wpisz liczbę.',
                            )),
                    new Assert\Length(array('min' => 1))
                    
                )
            )
        )
        ->add(
            'post',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Regex(array(
                                'pattern' => '/^[0-9]{2}-[0-9]{3}$/',
                                'htmlPattern' => '^[0-9]+$',
                                'message'
                                    => 'Wpisz poprawny kod pocztowy np.: 32-808',
                            )),
                    new Assert\Length(array('min' => 6, 'max' => 6))
                )
            )
        )
        ->add(
            'detail_id',
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
        return 'userForm';
    }
}
