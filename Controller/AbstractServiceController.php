<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Controller;

/**
 * Ebből a controllerből kell származnia az olyan controllereknek amik nem az oldalra
 * jelenítenek meg tartalmat. Azért fontos, mert minden egyéb esetben nyelvváltás is történik,
 * míg az ilyen controllerek esetében nem!
 *
 * Például: letöltés, képgenerálás, stb
 *
 * Class AbstractServiceController
 */
abstract class AbstractServiceController extends AbstractController
{
}
