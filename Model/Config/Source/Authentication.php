<?php
namespace Hostinghelden\Smtp\Model\Config\Source;
use Magento\Framework\Option\ArrayInterface;
class Authentication implements ArrayInterface {
  /**
   * @return array
   */
  public function toOptionArray() {
    $options = [
      [
        'value' => '',
        'label' => 'NONE'
      ], [
        'value' => 'plain',
        'label' => 'PLAIN'
      ], [
        'value' => 'login',
        'label' => 'LOGIN'
      ]
    ];
    return $options;
  }
}
