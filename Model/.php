<?php
namespace Hostinghelden\Topmenu\Block\Html;

class Topmenu extends \Magento\Theme\Block\Html\Topmenu
{
  /**
   * Get block cache life time
   *
   * @return int
   * @since 100.1.0
   */
  protected function getCacheLifetime()
  {
    return 0;
  }
}
