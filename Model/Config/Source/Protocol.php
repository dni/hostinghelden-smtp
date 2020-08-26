<?php
namespace Hostinghelden\Smtp\Model\Config\Source;
use Magento\Framework\Option\ArrayInterface;
class Protocol implements ArrayInterface {
  /**
   * @return array
   */
  public function toOptionArray() {
    $options = [
      [
        'value' => '',
        'label' => 'none'
      ], [
        'value' => 'ssl',
        'label' => 'SSL'
      ], [
        'value' => 'tls',
        'label' => 'TLS'
      ],
    ];
    return $options;
  }
}
