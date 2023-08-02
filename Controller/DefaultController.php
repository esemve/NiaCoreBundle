<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Controller;

use Nia\CoreBundle\Collections\CriteriaCollection;
use Nia\CoreBundle\Manager\PersistableManager;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    public function indexAction(): Response
    {
        //dump($this->getDoctrine()->getManager());

        $criteria = [];
        $criteria[] = ['id', '=', 2];
        $criteria = new CriteriaCollection($criteria);

        $user = $this->getUserManger()->findOneBy($criteria);

        dump($user);
        /*
                dump($this->getTranslator()->trans('NiaCoreBundle@hello'));
                $user = $this->getUserManger()->findOneBy($criteria);

                $groups = [];
                $i = 0;
                foreach ($user->getGroups() as $group) {
                    $groups[$i] = $group;
                    ++$i;
                }

                $user->addGroup($this->getUserGroupManager()->findById(2));

        */
        //dump($user->getGroups());
        //$this->getUserManger()->save($user);

        //dump($user);die();
        //$serial = serialize($user);

        //dump(unserialize($serial));
        //$uns = unserialize($serial);
        //dump($this->getUserManger()->refresh($uns));

        return new Response('<html><body>Nia</body></html>');
    }

    public function getUserManger(): PersistableManager
    {
        return $this->container->get('nia.user.user.manager');
    }

    public function getUserGroupManager(): PersistableManager
    {
        return $this->container->get('nia.user.user_group.manager');
    }
}
