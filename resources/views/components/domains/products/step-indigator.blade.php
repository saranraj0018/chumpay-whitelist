<div class="flex justify-between items-center gap-4 mb-4">
    <template x-for="(step, index) in steps" :key="index">
        <div class="flex-1 text-center">
            <div :class="{
                'bg-[#d98c33]/50 text-primary-foreground': stepNumber === index,
                'bg-muted text-muted-foreground': stepNumber !== index
            }"
                class="px-2 py-1 rounded-full text-sm font-medium transition-colors">
                <span x-text="step"></span>
            </div>
        </div>
    </template>
</div>
