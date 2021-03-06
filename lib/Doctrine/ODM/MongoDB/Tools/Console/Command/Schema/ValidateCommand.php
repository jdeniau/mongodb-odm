<?php

namespace Doctrine\ODM\MongoDB\Tools\Console\Command\Schema;

use Doctrine\Common\Cache\VoidCache;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends Command
{
    /**
     * @see \Symfony\Component\Console\Command\Command
     */
    protected function configure()
    {
        $this
            ->setName('odm:schema:validate')
            ->setDescription('Validates if document mapping stays the same after serializing into cache.')
            ->setDefinition(array())
            ->setHelp(<<<EOT
Validates if document mapping stays the same after serializing into cache.
EOT
            );
    }

    /**
     * @see \Symfony\Component\Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DocumentManager $dm */
        $dm = $this->getHelper('documentManager')->getDocumentManager();
        $metadataFactory = $dm->getMetadataFactory();
        $metadataFactory->setCacheDriver(new VoidCache());

        $errors = 0;
        foreach ($metadataFactory->getAllMetadata() as $meta) {
            if ($meta != unserialize(serialize($meta))) {
                ++$errors;
                $output->writeln(sprintf("%s has mapping issues.", $meta->name));
            }
        }
        if ($errors) {
            $output->writeln(sprintf('<error>%d document(s) have mapping issues.</error>'));
        } else {
            $output->writeln('All documents are OK!');
        }

        return $errors ? 255 : 0;
    }
}
