<?php
/**
 * Template for displaying form fields of post type settings
 *
 * @author ThimPress
 * @package Frontend_Editor/Templates
 * @version 3.0.0
 */

defined('ABSPATH') or die;
?>

<script type="text/x-template" id="tmpl-e-form-field-file-advanced">
	<li class="e-form-field file-advanced">
		<label>{{field.name}}</label>
        <div class="e-form-field-input">

            <input class="rwmb-file_advanced" name="_lp_attachments" v-model="settings[field.id]" type="hidden" data-options="{&quot;mimeType&quot;:&quot;&quot;,&quot;maxFiles&quot;:0,&quot;forceDelete&quot;:false,&quot;maxStatus&quot;:true}">
            <div class="rwmb-media-view">
                <ul class="rwmb-media-list ui-sortable" id="fe-assignment-attach-contain">
                    <li v-for="(attachment, attachmentIndex) in attachments" :key="attachmentIndex" class="rwmb-media-item attachment" :id="'fe-assignment-attach-list-' + attachment.id">
                        <div class="rwmb-media-preview attachment-preview">
                            <div class="rwmb-media-content thumbnail">
                                <div class="centered">
                                    <img :src="attachment.icon">
                                </div>
                            </div>
                        </div>
                        <div class="rwmb-media-info">
                            <a :href="attachment.url" class="rwmb-media-title" target="_blank">
                                {{attachment.filename}}
                            </a>
                            <a @click="_remove_attact($event, attachment.id)" :data-attachid="attachment.id"><span class="dashicons dashicons-trash"></span></a>
                        </div>
                    </li>
                </ul>
                <div class="rwmb-media-status">

                </div><div class="rwmb-media-add">
                    <a class="button" @click="_selectMedia($event)">+ Add Media</a>
                </div>
                <input type="hidden" class="rwmb-field-name" value="_lp_attachments">
            </div><p class="e-form-field-desc">{{field.desc}}</p>
        </div>
	</li>
</script>