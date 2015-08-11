<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\CustomerMetadataManagementInterface;
use Magento\Customer\Api\AddressMetadataManagementInterface;

class AttributeRepository
{
    const BILLING_ADDRESS_PREFIX = 'billing_';

    /** @var [] */
    protected $attributes;

    /** @var CustomerMetadataInterface */
    protected $customerMetadata;

    /** @var AddressMetadataInterface */
    protected $addressMetadata;

    /** @var CustomerMetadataManagementInterface */
    protected $customerMetadataManagement;

    /** @var AddressMetadataManagementInterface */
    protected $addressMetadataManagement;

    /**
     * @param CustomerMetadataManagementInterface $customerMetadataManagement
     * @param AddressMetadataManagementInterface $addressMetadataManagement
     * @param CustomerMetadataInterface $customerMetadata
     * @param AddressMetadataInterface $addressMetadata
     */
    public function __construct(
        CustomerMetadataManagementInterface $customerMetadataManagement,
        AddressMetadataManagementInterface $addressMetadataManagement,
        CustomerMetadataInterface $customerMetadata,
        AddressMetadataInterface $addressMetadata
    ) {
        $this->customerMetadataManagement = $customerMetadataManagement;
        $this->addressMetadataManagement = $addressMetadataManagement;
        $this->customerMetadata = $customerMetadata;
        $this->addressMetadata = $addressMetadata;
    }

    /**
     * @return array
     */
    public function getList()
    {
        if (!$this->attributes) {
            $this->attributes = $this->getListForEntity(
                $this->customerMetadata->getAllAttributesMetadata(),
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                $this->customerMetadataManagement
            );
            $this->attributes = array_merge(
                $this->attributes,
                $this->getListForEntity(
                    $this->addressMetadata->getAllAttributesMetadata(),
                    AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                    $this->addressMetadataManagement
                )
            );
        }

        return $this->attributes;
    }

    /**
     * @param array $metadata
     * @param string $entityTypeCode
     * @param \Magento\Customer\Api\MetadataManagementInterface $management
     * @return array
     */
    protected function getListForEntity(array $metadata, $entityTypeCode, $management)
    {
        $attributes = [];
        /** @var AttributeMetadataInterface $attribute */
        foreach ($metadata as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($entityTypeCode == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
                $attributeCode = self::BILLING_ADDRESS_PREFIX . $attribute->getAttributeCode();
            }
            $attributes[$attributeCode] = [
                AttributeMetadataInterface::ATTRIBUTE_CODE => $attributeCode,
                AttributeMetadataInterface::FRONTEND_INPUT => $attribute->getFrontendInput(),
                AttributeMetadataInterface::FRONTEND_LABEL => $attribute->getFrontendLabel(),
                AttributeMetadataInterface::BACKEND_TYPE => $attribute->getBackendType(),
                AttributeMetadataInterface::OPTIONS => $this->getOptionArray($attribute->getOptions()),
                AttributeMetadataInterface::IS_USED_IN_GRID => $attribute->getIsUsedInGrid(),
                AttributeMetadataInterface::IS_VISIBLE_IN_GRID => $attribute->getIsVisibleInGrid(),
                AttributeMetadataInterface::IS_FILTERABLE_IN_GRID => $management->canBeFilterableInGrid($attribute),
                AttributeMetadataInterface::IS_SEARCHABLE_IN_GRID => $management->canBeSearchableInGrid($attribute),
            ];
        }

        return $attributes;
    }

    protected function getOptionArray(array $options)
    {
        /** @var \Magento\Customer\Api\Data\OptionInterface $option */
        foreach ($options as &$option) {
            $option = ['label' => $option->getLabel(), 'value' => $option->getValue()];
        }
        return $options;
    }


    /**
     * @param string $code
     * @return []
     */
    public function getMetadataByCode($code)
    {
        return isset($this->getList()[$code]) ? $this->getList()[$code] : null;
    }
}
