<?php if (isset($component)) { $__componentOriginal79ac1b273b0697ab0b46c5b36e89f6b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79ac1b273b0697ab0b46c5b36e89f6b3 = $attributes; } ?>
<?php $component = App\View\Components\InstallerLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('installer-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\InstallerLayout::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">System Check</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Verifying your server environment.</p>
        </div>

        <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php $__currentLoopData = $requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $pass): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex py-4">
                    <div class="ml-3 flex flex-grow flex-col">
                        <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($label); ?></span>
                    </div>
                    <div>
                        <?php if($pass): ?>
                             <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                OK
                            </span>
                        <?php else: ?>
                             <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                FAIL
                            </span>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>

        <div class="flex justify-end">
            <?php if($allMet): ?>
                <a href="<?php echo e(route('install.database')); ?>" class="flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Next: Database Configuration
                </a>
            <?php else: ?>
                <button disabled class="flex w-full justify-center rounded-md border border-transparent bg-gray-400 py-2 px-4 text-sm font-medium text-white shadow-sm cursor-not-allowed">
                    Please fix requirements to proceed
                </button>
            <?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79ac1b273b0697ab0b46c5b36e89f6b3)): ?>
<?php $attributes = $__attributesOriginal79ac1b273b0697ab0b46c5b36e89f6b3; ?>
<?php unset($__attributesOriginal79ac1b273b0697ab0b46c5b36e89f6b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79ac1b273b0697ab0b46c5b36e89f6b3)): ?>
<?php $component = $__componentOriginal79ac1b273b0697ab0b46c5b36e89f6b3; ?>
<?php unset($__componentOriginal79ac1b273b0697ab0b46c5b36e89f6b3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\orbitdocs\resources\views/install/welcome.blade.php ENDPATH**/ ?>