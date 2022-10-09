<?php

namespace App\Tests\Unit\State\Product;


use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Enum\UserRole;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\UserRepository;
use App\State\Product\ProductProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;

class ProductProcessorTest extends KernelTestCase
{

    private readonly EntityManagerInterface|ObjectProphecy $em;
    private readonly ObjectProphecy|RouterInterface $router;
    private readonly ProcessorInterface|ObjectProphecy $decorated;

    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->router = $this->prophesize(RouterInterface::class);
        $this->decorated = $this->prophesize(ProcessorInterface::class);
        $this->em = $this->prophesize(EntityManagerInterface::class);
    }


    public function testProcessNewSaleProductCreatesNotification()
    {
        $operation = $this->prophesize(HttpOperation::class);
        $operation->getMethod()->shouldBeCalledOnce()->willReturn(HttpOperation::METHOD_POST);
        $repository = $this->prophesize(UserRepository::class);
        $repository->findByRole(UserRole::ROLE_CUSTOMER)->willReturn([new User(), new User()]);


        $this->em->getRepository(User::class)->willReturn($repository->reveal());

        $product = $this->getProduct();
        $product->discountPercent = 30;

        $this->router->generate('front_products_item', ['reference' => $product->reference])->willReturn(
            "/toto/123456"
        );

        // 2 users, 2 calls
        $this->em->persist(Argument::any())->shouldBeCalledTimes(2);
        $this->em->flush()->shouldBeCalledOnce();

        $processor = $this->getProcessor();

        $this->decorated->process($product, $operation->reveal(), [], [])->willReturn($product);

        $processor->process($product, $operation->reveal(), [], []);
    }


    public function testProcessNewProductDoesNotCreatesNotification()
    {
        $operation = $this->prophesize(HttpOperation::class);
        $operation->getMethod()->shouldBeCalledOnce()->willReturn(HttpOperation::METHOD_POST);

        $product = $this->getProduct();

        $this->em->persist(Argument::any())->shouldNotBeCalled();
        $this->em->flush()->shouldNotBeCalled();

        $processor = $this->getProcessor();

        $this->decorated->process($product, $operation->reveal(), [], [])->willReturn($product);

        $processor->process($product, $operation->reveal(), [], []);
    }

    public function testProcessUpdatedSaleProductCreatesNotification()
    {
        $operation = $this->prophesize(HttpOperation::class);
        $operation->getMethod()->shouldBeCalledOnce()->willReturn(HttpOperation::METHOD_PUT);
        $repository = $this->prophesize(UserRepository::class);
        $repository->findByRole(UserRole::ROLE_CUSTOMER)->willReturn([new User(), new User()]);


        $this->em->getRepository(User::class)->willReturn($repository->reveal());

        $product = $this->getProduct();

        $previous = clone $product;
        $previous->discountPercent = 10;

        $product->discountPercent = 30;

        $this->router->generate('front_products_item', ['reference' => $product->reference])->willReturn(
            "/toto/123456"
        );

        // 2 users, 2 calls
        $this->em->persist(Argument::any())->shouldBeCalledTimes(2);
        $this->em->flush()->shouldBeCalledOnce();

        $processor = $this->getProcessor();

        $context = [
            "previous_data" => $previous
        ];

        $this->decorated->process($product, $operation->reveal(), [], $context)->willReturn($product);

        $processor->process($product, $operation->reveal(), [], $context);
    }


    public function testProcessUpdatedProductNameDoesNotCreatesNotification()
    {
        $operation = $this->prophesize(HttpOperation::class);
        $operation->getMethod()->shouldBeCalledOnce()->willReturn(HttpOperation::METHOD_PUT);

        $product = $this->getProduct();

        $previous = clone $product;
        $previous->discountPercent = 30;

        $product->name = "toto";
        $product->discountPercent = 30;

        $this->em->persist(Argument::any())->shouldNotBeCalled();
        $this->em->flush()->shouldNotBeCalled();

        $processor = $this->getProcessor();

        $context = [
            "previous_data" => $previous
        ];

        $this->decorated->process($product, $operation->reveal(), [], $context)->willReturn($product);

        $processor->process($product, $operation->reveal(), [], $context);
    }


    private function getProduct(): Product
    {
        $product = new Product();

        $product->reference = "P_123456";
        $product->price = 30;

        return $product;
    }


    private function getProcessor(): ProductProcessor
    {
        return new ProductProcessor($this->em->reveal(), $this->decorated->reveal(), $this->router->reveal());
    }


}
