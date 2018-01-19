<?php
// tests/Form/Type/TestedTypeTest.php
namespace App\Tests\Form;

use AppBundle\Form\TaskType;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Component\Form\Test\TypeTestCase;

class UserTypeTest extends TypeTestCase
{
    public function test_SubmitValidDataUser()
    {
        $formData = array(
            'roles' => ['ROLE_ADMIN'],
            'username' => 'jojo',
            'password' => array('first' => 'jojo', 'second' => 'jojo'),
            'email' => 'jojo@jojo.com'
        );

        $form = $this->factory->create(UserType::class);

        $object = new User();
        $object->setRoles(['ROLE_ADMIN']);
        $object->setUsername('jojo');
        $object->setPassword('jojo');
        $object->setEmail('jojo@jojo.com');


        // submit the data to the form directly

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object->getRoles(), $form->get('roles')->getData());
        $this->assertEquals($object->getUsername(), $form->get('username')->getData());
        $this->assertEquals($object->getPassword(), $form->get('password')->getData());
        $this->assertEquals($object->getEmail(), $form->get('email')->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}