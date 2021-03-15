<?php
declare(strict_types=1);

namespace OH\ProductPreview\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Preview
 * @package OH\ProductPreview\Block\Adminhtml\Product\Edit\Button
 */
class Preview implements ButtonProviderInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * @var UrlInterface
     */
    private UrlInterface $frontendUrlBuilder;

    public function __construct(
        ProductRepository $productRepository,
        RequestInterface $request,
        Url $frontendUrlBuilder
    )
    {
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData(): array
    {
        $id = (int)$this->request->getParam('id');

        if ($this->request->getActionName() != 'new' && $this->canShow($id)) {
            return [
                'label' => __('Open preview'),
                'on_click' => sprintf("window.open('%s');", $this->getFrontendUrl('catalog/product/view', null, null, ['id' => $id])),
                'class' => 'action-secondary',
                'sort_order' => 10
            ];
        }

        return [];
    }

    /**
     * Preview button only available if product is enabled
     *
     * @param $productId
     * @return bool
     */
    private function canShow($productId): bool
    {
        try {
            $product = $this->productRepository->getById($productId);
            return $product->getStatus() == Status::STATUS_ENABLED;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Get frontend url
     *
     * @param string $routePath
     * @param null $scope
     * @param null $store
     * @param null $params
     * @return string
     */
    public function getFrontendUrl(string $routePath, $scope = null, $store = null, $params = null): string
    {
        if ($scope) {
            $this->frontendUrlBuilder->setScope($scope);
            $paramsOrg = [
                '_current' => false,
                '_nosid' => true,
                '_query' => [\Magento\Store\Model\StoreManagerInterface::PARAM_NAME => $store]
            ];

            $href = $this->frontendUrlBuilder->getUrl(
                $routePath,
                $params ? array_merge($params, $paramsOrg) : $paramsOrg
            );
        } else {
            $paramsOrg = [
                '_current' => false,
                '_nosid' => true
            ];

            $href = $this->frontendUrlBuilder->getUrl(
                $routePath,
                $params ? array_merge($params, $paramsOrg) : $paramsOrg
            );
        }

        return $href;
    }
}
