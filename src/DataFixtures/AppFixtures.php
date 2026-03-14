<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Créer des utilisateurs
        $adminUser = new User();
        $adminUser->setEmail('admin@example.com');
        $adminUser->setFullName('Admin User');
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin123'));
        $adminUser->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $adminUser->setIsActive(true);
        $manager->persist($adminUser);

        $userUser = new User();
        $userUser->setEmail('user@example.com');
        $userUser->setFullName('Regular User');
        $userUser->setPassword($this->passwordHasher->hashPassword($userUser, 'user123'));
        $userUser->setRoles(['ROLE_USER']);
        $userUser->setIsActive(true);
        $manager->persist($userUser);

        // Créer des catégories
        $cats = [];
        $names = ['Électronique', 'Maison', 'Jouets', 'Livres'];
        foreach ($names as $name) {
            $c = new Category();
            $c->setName($name);
            $c->setDescription($name . ' category');
            $manager->persist($c);
            $cats[] = $c;
        }

        // Créer des produits
        $products = [];
        for ($i = 1; $i <= 12; $i++) {
            $p = new Product();
            $p->setName('Produit ' . $i);
            $p->setDescription('Description pour produit ' . $i);
            $p->setPrice(number_format(mt_rand(1000, 10000) / 100, 2, '.', ''));
            $p->setQuantity(mt_rand(0, 20));
            $p->setCategory($cats[array_rand($cats)]);
            $manager->persist($p);
            $products[] = $p;
        }

        // Créer des clients
        $customers = [];
        for ($i = 1; $i <= 5; $i++) {
            $cust = new Customer();
            $cust->setCustomerId('CUST' . str_pad($i, 3, '0', STR_PAD_LEFT));
            $cust->setCustomerName('Client Exemple ' . $i);
            $cust->setSegment(['Premium', 'Standard', 'Budget'][array_rand(['Premium', 'Standard', 'Budget'])]);
            $cust->setCountry('France');
            $cust->setCity(['Paris', 'Lyon', 'Marseille', 'Toulouse'][array_rand(['Paris', 'Lyon', 'Marseille', 'Toulouse'])]);
            $cust->setState('Région ' . $i);
            $cust->setRegion('Région ' . $i);
            $cust->setPostalCode(str_pad(75000 + $i * 100, 5, '0', STR_PAD_LEFT));
            $manager->persist($cust);
            $customers[] = $cust;
        }

        // Créer des commandes
        for ($i = 0; $i < 3; $i++) {
            $order = new Order();
            $order->setOrderNumber('ORD-' . uniqid() . '-' . date('YmdHis'));
            $order->setCustomer($customers[array_rand($customers)]);
            $order->setStatus(['pending', 'confirmed', 'shipped', 'delivered'][array_rand(['pending', 'confirmed', 'shipped', 'delivered'])]);

            // Ajouter 1-3 items à la commande
            $total = 0;
            $itemCount = mt_rand(1, 3);
            for ($j = 0; $j < $itemCount; $j++) {
                $item = new OrderItem();
                $product = $products[array_rand($products)];
                $item->setProduct($product);
                $item->setUnitPrice($product->getPrice());
                $item->setQuantity(mt_rand(1, 5));
                $item->calculateLineTotal();
                $total += (float) $item->getLineTotal();
                $order->addOrderItem($item);
                $manager->persist($item);
            }

            $order->setTotal((string) $total);
            $manager->persist($order);
        }

        $manager->flush();
    }
}
