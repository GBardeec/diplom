<template>
    <div class="tree-node" :style="{ marginLeft: level * 20 + 'px' }">
        <div
            @click="toggleNode"
            class="tree-node-content"
            :class="{ 'has-children': hasChildren, 'selected': isSelected }"
        >
            <div class="flex items-center gap-2 flex-1">
                <span class="tree-toggle" v-if="hasChildren">
                    {{ isExpanded ? '📂' : '📁' }}
                </span>
                <span class="tree-icon">{{ getNodeIcon(node) }}</span>
                <div class="flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold text-white">{{ node.title }}</span>
                    </div>
                    <div class="text-xs text-white/60 mt-1">
                        <span class="mr-3">📊 {{ vacancyFoundLabel(node.vacancies_count) }}</span>
                        <span v-if="node.salary_stats?.avg_salary" class="mr-3">
                            💰 {{ formatSalaryValue(node.salary_stats.avg_salary) }}
                        </span>
                        <span v-if="node.children && node.children.length">
                            👥 {{ node.children.length }} подразделов
                        </span>
                    </div>
                </div>
                <button
                    @click.stop="showDetails(node)"
                    class="px-3 py-1 text-sm bg-white/10 hover:bg-white/20 rounded-lg transition"
                >
                    Подробнее →
                </button>
            </div>
        </div>

        <!-- Дочерние узлы (рекурсия) -->
        <div v-if="hasChildren && isExpanded" class="tree-children">
            <HierarchyNode
                v-for="child in node.children"
                :key="child.id"
                :node="child"
                :level="level + 1"
                :selected-id="selectedId"
                @select="handleSelect"
                @show-details="handleShowDetails"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    node: { type: Object, required: true },
    level: { type: Number, default: 0 },
    selectedId: { type: Number, default: null }
});

const emit = defineEmits(['select', 'show-details']);
const vacancyFoundLabel = (value) => {
    const count = Number(value) || 0;
    const mod10 = count % 10;
    const mod100 = count % 100;
    const word = mod10 === 1 && mod100 !== 11 ? 'вакансия' : (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14) ? 'вакансии' : 'вакансий');
    return `${count} ${word} ${mod10 === 1 && mod100 !== 11 ? 'найдена' : 'найдено'}`;
};

const isExpanded = ref(true);
const hasChildren = computed(() => props.node.children && props.node.children.length > 0);
const isSelected = computed(() => props.selectedId === props.node.id);

const toggleNode = () => {
    if (hasChildren.value) {
        isExpanded.value = !isExpanded.value;
    }
    emit('select', props.node);
};

const handleSelect = (node) => {
    emit('select', node);
};

const handleShowDetails = (node) => {
    emit('show-details', node);
};

const showDetails = (node) => {
    emit('show-details', node);
};

const getNodeIcon = (node) => {
    const icons = {
        'Архитектор': '🏛️', 'Директор': '👔', 'Менеджер': '📋', 'Разработчик': '💻',
        'Тестировщик': '🔍', 'Аналитик': '📊', 'Дизайнер': '🎨', 'Инженер': '🔧',
        'Администратор': '🖥️', 'Программист': '💻', 'DevOps': '⚙️', 'Data Scientist': '🧠',
        'HR': '👥', 'Маркетолог': '📢', 'Продажи': '💰', 'Поддержка': '🆘'
    };

    for (const [key, icon] of Object.entries(icons)) {
        if (node.title?.includes(key)) return icon;
    }

    // Иконки по уровню
    if (node.level === 0) return '👔';
    if (node.level === 1) return '🏛️';
    if (node.level === 2) return '📋';
    if (node.level === 3) return '⭐';
    if (node.level === 4) return '👨‍💻';
    return '📌';
};

const getLevelLabel = (level) => {
    const labels = [
        'Топ-менеджмент',
        'Директора / Архитекторы',
        'Руководители отделов',
        'Ведущие специалисты',
        'Специалисты',
        'Младшие специалисты'
    ];
    return labels[level] || `Уровень ${level + 1}`;
};

const getLevelBadgeClass = (level) => {
    const classes = {
        0: 'bg-purple-600/30 text-purple-300',
        1: 'bg-blue-600/30 text-blue-300',
        2: 'bg-emerald-600/30 text-emerald-300',
        3: 'bg-yellow-600/30 text-yellow-300',
        4: 'bg-orange-600/30 text-orange-300',
        5: 'bg-gray-600/30 text-gray-300'
    };
    return classes[level] || 'bg-white/10 text-white/70';
};

const formatSalaryValue = (value) => {
    if (!value || value === 0) return 'Не указано';
    return new Intl.NumberFormat('ru-RU').format(value) + ' ₽';
};
</script>

<style scoped>
.tree-node {
    margin-bottom: 8px;
}

.tree-node-content {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.tree-node-content:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(139, 92, 246, 0.4);
    transform: translateX(4px);
}

.tree-node-content.selected {
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(236, 72, 153, 0.2));
    border-color: rgba(139, 92, 246, 0.6);
    box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
}

.tree-toggle {
    font-size: 1.2rem;
    cursor: pointer;
    transition: transform 0.2s;
    margin-right: 8px;
}

.tree-icon {
    font-size: 1.5rem;
    margin-right: 12px;
}

.tree-children {
    margin-top: 8px;
}

/* Анимация для раскрытия */
.tree-children-enter-active,
.tree-children-leave-active {
    transition: all 0.3s ease;
}

.tree-children-enter-from,
.tree-children-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
