<?php

declare(strict_types=1);

namespace Nia\CoreBundle\Form\Traits;

trait DataMapperTrait
{
    /**
     * Az automatikusan mappelhetö egyszerü fieldeket adja vissza egy tömbben
     * A tömb felépítése:
     *  'username' => 'username'
     * Ez azt jelenti, hogy az username nevü field -re (ez a kulcs) az entityn (ez az ertek)
     * a setUsername() illetve a getUsername() methodot kell hivni.
     *
     * @return array
     */
    protected function getDataMap(): array
    {
        return [];
    }

    /**
     * @param $forms
     * @param $data
     *
     * @return \Symfony\Component\Form\FormInterface[] array
     */
    protected function simpleMapDataToForms($data, $forms): array
    {
        $forms = iterator_to_array($forms);
        foreach ($this->getDataMap() as $fieldName => $entityFunctionName) {
            try {
                $functionName = 'get'.ucfirst($entityFunctionName);
                if (method_exists($data, $functionName)) {
                    $forms[$fieldName]->setData($data->{$functionName}());
                }
            } catch (\Exception $e) {
            }
        }

        return $forms;
    }

    /**
     * @param $forms
     * @param $data
     *
     * @return \Symfony\Component\Form\FormInterface[] array
     */
    protected function simpleMapFormsToData($forms, &$data): array
    {
        $forms = iterator_to_array($forms);
        foreach ($this->getDataMap() as $fieldName => $entityFunctionName) {
            if (('id' !== $fieldName) && ('id' !== $entityFunctionName)) {
                if (!empty($forms[$fieldName]->getData())) {
                    $functionName = 'set'.ucfirst($entityFunctionName);
                    if (method_exists($data, $functionName)) {
                        $data->{$functionName}($forms[$fieldName]->getData());
                    }
                }
            }
        }

        return $forms;
    }
}
