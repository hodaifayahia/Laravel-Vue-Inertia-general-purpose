#!/bin/bash
cd /home/houdaifayahia/www/Laravel-Vue-Inertia-general-purpose

# Fix all three route calls
sed -i "s|form\.post(route('provider\.availability\.store')|form.post(store().url|g" resources/js/pages/Provider/Availability/Index.vue
sed -i "s|bulkForm\.post(route('provider\.availability\.bulk')|bulkForm.post(bulk().url|g" resources/js/pages/Provider/Availability/Index.vue
sed -i "s|router\.delete(route('provider\.availability\.destroy')|router.delete(destroy().url|g" resources/js/pages/Provider/Availability/Index.vue

echo "Routes fixed successfully!"
