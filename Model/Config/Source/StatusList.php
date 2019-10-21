<?php

namespace Videoscm\MailChimpImport\Model\Config\Source;

class StatusList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'pending' => __('Pending'),
            'subscribed' => __('Subscribed'),
            'unsubscribed' => __('Unsubscribed')
        ];
    }
}
