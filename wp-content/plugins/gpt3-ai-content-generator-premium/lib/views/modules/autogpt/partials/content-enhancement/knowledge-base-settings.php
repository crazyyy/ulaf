<?php
/**
 * Partial: Content Enhancement Automated Task - Knowledge Base Settings
 * Updated to match Content Writer knowledge base popover styles.
 *
 * @since 2.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables available from parent:
// $openai_vector_stores, $pinecone_indexes, $qdrant_collections
// $openai_embedding_models, $google_embedding_models, $is_pro
?>
<div class="aipkit_vector_settings_redesigned">
    <div class="aipkit_vector_settings_chunk aipkit_vector_settings_chunk--enable">
        <div class="aipkit_vector_settings_chunk_body">
            <div class="aipkit_vector_toggle_card">
                <div class="aipkit_vector_toggle_row">
                    <div class="aipkit_vector_toggle_info">
                        <span class="aipkit_vector_toggle_label"><?php esc_html_e('Enable Knowledge Base', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_vector_toggle_desc"><?php esc_html_e('Use your vector stores for RAG-enhanced content', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <div class="aipkit_vector_toggle_controls">
                        <label class="aipkit_switch">
                            <input
                                type="checkbox"
                                id="aipkit_task_ce_enable_vector_store"
                                name="ce_enable_vector_store"
                                class="aipkit_toggle_switch aipkit_task_ce_vector_store_toggle"
                                value="1"
                                <?php disabled(!$is_pro); ?>
                            >
                            <span class="aipkit_switch_slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="aipkit_vector_settings_chunk aipkit_vector_settings_chunk--source aipkit_task_ce_vector_store_settings_container" style="display:none;">
        <div class="aipkit_vector_settings_chunk_body">
            <div class="aipkit_vector_source_selector">
                <div class="aipkit_vector_source_row">
                    <label class="aipkit_vector_settings_label" for="aipkit_task_ce_vector_store_provider">
                        <?php esc_html_e('Provider', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_task_ce_vector_store_provider"
                        name="ce_vector_store_provider"
                        class="aipkit_vector_settings_select aipkit_task_ce_vector_store_provider_select"
                    >
                        <option value="openai" selected>OpenAI</option>
                        <option value="pinecone">Pinecone</option>
                        <option value="qdrant">Qdrant</option>
                    </select>
                </div>
                <div class="aipkit_vector_source_divider">
                    <span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
                </div>
                <div class="aipkit_vector_source_row aipkit_vector_source_row--store">
                    <div class="aipkit_cw_vector_openai_field aipkit_task_ce_vector_openai_field">
                        <label class="aipkit_vector_settings_label" for="aipkit_task_ce_openai_vector_store_ids">
                            <?php esc_html_e('Vector Store', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <div
                            class="aipkit_popover_multiselect aipkit_vector_multiselect"
                            data-aipkit-vector-stores-dropdown
                            data-placeholder="<?php echo esc_attr__('Select stores', 'gpt3-ai-content-generator'); ?>"
                            data-selected-label="<?php echo esc_attr__('selected', 'gpt3-ai-content-generator'); ?>"
                        >
                            <button
                                type="button"
                                class="aipkit_popover_multiselect_btn aipkit_vector_multiselect_btn"
                                aria-expanded="false"
                                aria-controls="aipkit_task_ce_openai_vector_store_panel"
                            >
                                <span class="aipkit_popover_multiselect_label">
                                    <?php esc_html_e('Select stores', 'gpt3-ai-content-generator'); ?>
                                </span>
                            </button>
                            <div
                                id="aipkit_task_ce_openai_vector_store_panel"
                                class="aipkit_popover_multiselect_panel"
                                role="menu"
                                hidden
                            >
                                <div class="aipkit_popover_multiselect_options"></div>
                            </div>
                        </div>
                        <select
                            id="aipkit_task_ce_openai_vector_store_ids"
                            name="ce_openai_vector_store_ids[]"
                            class="aipkit_popover_multiselect_select"
                            multiple
                            size="3"
                            hidden
                            aria-hidden="true"
                            tabindex="-1"
                        >
                            <?php if (!empty($openai_vector_stores)): ?>
                                <?php foreach ($openai_vector_stores as $store): ?>
                                    <option value="<?php echo esc_attr($store['id']); ?>"><?php echo esc_html($store['name']); ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled><?php esc_html_e('No stores found', 'gpt3-ai-content-generator'); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="aipkit_task_ce_vector_pinecone_field" style="display:none;">
                        <label class="aipkit_vector_settings_label" for="aipkit_task_ce_pinecone_index_name">
                            <?php esc_html_e('Index', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <select
                            id="aipkit_task_ce_pinecone_index_name"
                            name="ce_pinecone_index_name"
                            class="aipkit_vector_settings_select"
                        >
                            <option value=""><?php esc_html_e('Select index', 'gpt3-ai-content-generator'); ?></option>
                            <?php if (!empty($pinecone_indexes)): ?>
                                <?php foreach ($pinecone_indexes as $index): ?>
                                    <option value="<?php echo esc_attr($index['name']); ?>"><?php echo esc_html($index['name']); ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled><?php esc_html_e('No indexes found', 'gpt3-ai-content-generator'); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="aipkit_task_ce_vector_qdrant_field" style="display:none;">
                        <label class="aipkit_vector_settings_label" for="aipkit_task_ce_qdrant_collection_name">
                            <?php esc_html_e('Collection', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <select
                            id="aipkit_task_ce_qdrant_collection_name"
                            name="ce_qdrant_collection_name"
                            class="aipkit_vector_settings_select"
                        >
                            <option value=""><?php esc_html_e('Select collection', 'gpt3-ai-content-generator'); ?></option>
                            <?php if (!empty($qdrant_collections)): ?>
                                <?php foreach ($qdrant_collections as $collection): ?>
                                    <option value="<?php echo esc_attr($collection['name']); ?>"><?php echo esc_html($collection['name']); ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled><?php esc_html_e('No collections found', 'gpt3-ai-content-generator'); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="aipkit_vector_settings_chunk aipkit_vector_settings_chunk--embedding aipkit_task_ce_vector_store_settings_container aipkit_task_ce_vector_embedding_config_row" style="display:none;">
        <div class="aipkit_vector_settings_chunk_body">
            <div class="aipkit_vector_embedding_selector">
                <div class="aipkit_vector_embedding_row">
                    <label class="aipkit_vector_settings_label" for="aipkit_task_ce_vector_embedding_provider">
                        <?php esc_html_e('Embed Provider', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_task_ce_vector_embedding_provider"
                        name="ce_vector_embedding_provider"
                        class="aipkit_vector_settings_select aipkit_task_ce_vector_embedding_provider_select"
                    >
                        <option value="openai" selected>OpenAI</option>
                        <option value="google">Google</option>
                        <option value="azure">Azure</option>
                        <option value="openrouter">OpenRouter</option>
                    </select>
                </div>
                <div class="aipkit_vector_embedding_divider">
                    <span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
                </div>
                <div class="aipkit_vector_embedding_row aipkit_vector_embedding_row--model">
                    <label class="aipkit_vector_settings_label" for="aipkit_task_ce_vector_embedding_model">
                        <?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_task_ce_vector_embedding_model"
                        name="ce_vector_embedding_model"
                        class="aipkit_vector_settings_select aipkit_task_ce_vector_embedding_model_select"
                    >
                        <option value=""><?php esc_html_e('Select provider first', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="aipkit_vector_settings_chunk aipkit_vector_settings_chunk--retrieval aipkit_vector_settings_chunk--collapsible aipkit_task_ce_vector_store_settings_container" style="display:none;">
        <button type="button" class="aipkit_vector_settings_chunk_header aipkit_vector_settings_chunk_header--collapsible" aria-expanded="false" aria-controls="aipkit_task_ce_retrieval_options_body">
            <span class="aipkit_vector_settings_chunk_icon">
                <span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
            </span>
            <span class="aipkit_vector_settings_chunk_title"><?php esc_html_e('Retrieval Options', 'gpt3-ai-content-generator'); ?></span>
            <span class="aipkit_vector_settings_chunk_toggle">
                <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
            </span>
        </button>

        <div class="aipkit_vector_settings_chunk_body aipkit_vector_settings_chunk_body--collapsible" id="aipkit_task_ce_retrieval_options_body" aria-hidden="true">
            <div class="aipkit_vector_slider_group">
                <div class="aipkit_vector_slider_header">
                    <label class="aipkit_vector_settings_label" for="aipkit_task_ce_vector_store_top_k">
                        <?php esc_html_e('Results Limit', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_vector_slider_value" id="aipkit_task_ce_vector_store_top_k_value">3</span>
                </div>
                <div class="aipkit_vector_slider_wrapper">
                    <div class="aipkit_vector_slider_labels">
                        <span class="aipkit_vector_slider_label aipkit_vector_slider_label--min">
                            <span class="dashicons dashicons-minus" aria-hidden="true"></span>
                            <?php esc_html_e('Fewer', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <span class="aipkit_vector_slider_label aipkit_vector_slider_label--max">
                            <?php esc_html_e('More', 'gpt3-ai-content-generator'); ?>
                            <span class="dashicons dashicons-plus" aria-hidden="true"></span>
                        </span>
                    </div>
                    <input
                        type="range"
                        id="aipkit_task_ce_vector_store_top_k"
                        name="ce_vector_store_top_k"
                        class="aipkit_vector_slider"
                        value="3"
                        min="1"
                        max="20"
                        step="1"
                    >
                </div>
                <p class="aipkit_vector_slider_hint"><?php esc_html_e('Number of matching chunks to retrieve', 'gpt3-ai-content-generator'); ?></p>
            </div>
        </div>
    </div>
</div>
