<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Transformer\Writer\Graph;

use phpDocumentor\Descriptor\DocumentationSetDescriptor;

interface Generator
{
    public function create(DocumentationSetDescriptor $documentationSet, string $filename): void;
}
