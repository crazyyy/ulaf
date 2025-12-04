import CF7AppsNotice from '../../../../../src/components/CF7AppsNotice';
import CF7AppsSkeletonLoader from '../components/CF7AppsSkeletonLoader';
import CF7AppsDisabledOverlay from '../components/CF7AppsDisabledOverlay';
import { useParams } from 'react-router';
import { useState, useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { getApps, saveSettings } from '../api'
import CF7AppsTextField from '../../../../../src/components/CF7AppsTextField';
import CF7AppsNumberField from '../../../../../src/components/CF7AppsNumberField';
import CF7AppsToggle from '../../../../../src/components/CF7AppsToggle';
import CF7AppsSelectField from '../../../../../src/components/CF7AppsSelectField';
import { Button } from '@wordpress/components';
import CF7AppsRadioField from '../../../../../src/components/CF7AppsRadioField';
import parse from 'html-react-parser';
import {toast} from "react-toastify";

const CF7AppsSettings = () => {
    let { app } = useParams();

    const [ isLoading, setIsLoading ] = useState( true );
    const [ appSettings, setAppSettings ] = useState( false );
    const [ formData, setFormData ] = useState( {} );
    const [ isSaving, setIsSaving ] = useState( false );
    const [ notice, setNotice ] = useState( { show: false, text: '' } );

    app = app ? app : 'cf7-redirection';

    useEffect( () => {
        getApps( app, CF7AppsInternalSettings.formID )
            .then( ( appSettings ) => {
                let settings = appSettings['admin_settings']['general'];
                let _formData = {};
                Object.keys( settings['fields'] ).map( ( fieldKey ) => {
                    const field = settings['fields'][ fieldKey ];

                    if ( 'template' !== fieldKey ) {
                        if ( 'text' === field.type || 'number' === field.type || 'radio' === field.type ) {
                            if ( field.value ) {
                                _formData[ fieldKey ] = field.value;
                            } else {
                                _formData[ fieldKey ] = field.default;
                            }
                        } else if ( 'checkbox' === field.type ) {
                            if ( field.checked ) {
                                _formData[ fieldKey ] = field.checked;
                            } else {
                                _formData[ fieldKey ] = field.default;
                            }
                        }

                        if ( settings['fields'][ fieldKey ].sub_fields ) {
                            Object.keys( settings['fields'][ fieldKey ].sub_fields ).map( ( subFieldKey ) => {
                                const subField = settings['fields'][ fieldKey ].sub_fields[ subFieldKey ];
                                if ( subField.type === 'text' || subField.type === 'number' ) {

                                    if ( subField.value ) {
                                        _formData[ subFieldKey ] = subField.value;
                                    } else {
                                        _formData[ subFieldKey ] = subField.default;
                                    }

                                } else if ( subField.type === 'checkbox' ) {

                                    if ( subField.checked ) {
                                        _formData[ subFieldKey ] = subField.checked;
                                    } else {
                                        _formData[ subFieldKey ] = subField.default;
                                    }

                                } else if ( subField.type === 'select' ) {
                                    if ( subField.selected ) {
                                        _formData[ subFieldKey ] = subField.selected;
                                    } else {
                                        _formData[ subFieldKey ] = subField.default;
                                    }
                                }
                            } );
                        }

                    }

                } );

                setFormData( _formData );
                setAppSettings( appSettings );
                setIsLoading( false );

            } ).catch( () => {
                setIsLoading( false );
            } );
    }, [] );

    const handleInputChange = ( e ) => {
        const { name, value } = e.target;
        setFormData( ( prev ) => ( {
            ...prev,
            [ name ]: value,
        } ) );
    }

    const saveAppSettings = () => {

        let missingRequired = false;
        let requiredMessage = '';

        Object.keys(appSettings.admin_settings['general']['fields']).some((fieldKey) => {
            const field = appSettings.admin_settings['general']['fields'][fieldKey];
            if (field && field.required && (formData[fieldKey] === '' || formData[fieldKey] === undefined)) {
                missingRequired = true;
                requiredMessage = field.required_message || __( 'Please fill all required fields.', 'cf7apps' );
                return true;
            } else {

                if (field && field.sub_fields) {

                    if ( field.sub_fields[ formData[ fieldKey ] ] && field.sub_fields[ formData[ fieldKey ] ].required && (formData[ formData[ fieldKey ] ] === '' || formData[ formData[ fieldKey ] ] === undefined)) {
                        missingRequired = true;
                        requiredMessage = field.sub_fields[ formData[ fieldKey ] ].required_message || __( 'Please fill all required fields.', 'cf7apps' );
                        return true;
                    }


                }
            }

            return false;
        });

        if (missingRequired) {
            setNotice({ show: true, text: requiredMessage });
            toast.error( __( 'Error! Please fill all required fields.', 'cf7apps' ) );
            setIsSaving(false);
            return;
        }

        setIsSaving( true );
        saveSettings( app, formData, CF7AppsInternalSettings.formID )
            .then( response => {
                setIsSaving( false );
                toast.success( __( 'Great! Settings Saved Successfully', 'cf7apps' ) );
            } ).catch( error => {
                setIsSaving( false );
                toast.error( __( 'Error! Something Went Wrong', 'cf7apps' ) );
        } );
    };

    const Settings = () => {

        return (
            <div className={ 'cf7apps-form' }>
                <div className={ 'MuiTabPanel-root' }>
                    {
                        notice.show && <CF7AppsNotice
                            type={ 'warning' }
                            text={ notice.text }
                        />
                    }

                    {
                        Object.keys( appSettings.admin_settings['general']['fields'] ).map( fieldKey => {
                            const field = appSettings.admin_settings['general']['fields'][ fieldKey ];
                            const className =  undefined === field.class ? '' : field.class;
                            const help = field.help;
                            const placeholder = undefined === field.placeholder ? '' : field.placeholder;

                            if ( 'title' === fieldKey ) {
                                return (
                                    <h3>{ appSettings.admin_settings['general']['fields'].title }</h3>
                                );
                            } else if ( 'description' === fieldKey ) {
                                return (
                                    <p className={ 'cf7apps-help-text' }>
                                        { appSettings.admin_settings['general']['fields'].description }
                                    </p>
                                );
                            } else if ( 'notice' === field.type ) {
                                return (
                                    <CF7AppsNotice
                                        type={ className }
                                        text={ field.text }
                                    />
                                );
                            } else if ( 'text' === field.type ) {
                                return (
                                    <CF7AppsTextField
                                        label={ field.title }
                                        description={ parse( String( field.description ) ) }
                                        className={ className }
                                        placeholder={ placeholder }
                                        value={ formData[ fieldKey ] }
                                        name={ fieldKey }
                                        onChange={ handleInputChange }
                                        required={ field.required }
                                        disabled={ field.disabled }
                                    />
                                );
                            } else if ( 'number' === field.type ) {
                                return (
                                    <CF7AppsNumberField
                                        label={ field.title }
                                        description={ parse( String( field.description ) ) }
                                        className={ className }
                                        name={ fieldKey }
                                        placeholder={ placeholder }
                                        value={ formData[ fieldKey ] }
                                        onChange={ handleInputChange }
                                        disabled={ field.disabled }
                                    />
                                );
                            } else if ( 'checkbox' === field.type ) {
                                return (
                                    <CF7AppsToggle
                                        help={ help }
                                        label={ field.title }
                                        className={ className }
                                        isSelected={ formData[ fieldKey ] }
                                        onChange={ ( e ) => {
                                            setFormData( {
                                                ...formData,
                                                [ fieldKey ]: ! formData[ fieldKey ]
                                            } );
                                        } }
                                        disabled={ field.disabled }
                                    />
                                );
                            } else if ( 'select' === field.type ) {
                                return (
                                    <CF7AppsSelectField
                                        label={ field.title }
                                        className={ className }
                                        name={ fieldKey }
                                        selected={ field['selected'] }
                                        options={ field.options }
                                        onChange={ handleInputChange }
                                        description={ parse( String( field.description ) ) }
                                        disabled={ field.disabled }
                                    />
                                )
                            } else if ( 'save_button' === field.type ) {
                                return (
                                    <div className="cf7apps-form-group">
                                        <Button
                                            className="cf7apps-btn tertiary-primary"
                                            onClick={ saveAppSettings }
                                            isBusy={ isSaving }
                                            disabled={ field.disabled }
                                        >
                                            { field.text }
                                        </Button>
                                    </div>
                                )
                            } else if ( 'template' === fieldKey ) {
                                console.log( field )
                            } else if ( 'radio' === field.type ) {

                                const subKey = formData[ fieldKey ];
                                const subField = field.sub_fields && field.sub_fields[ subKey ];
                                subField['value'] = formData[ subKey ];
                                return (
                                    <div key={ fieldKey }>
                                        <CF7AppsRadioField
                                            label={ field.title }
                                            className={ className }
                                            options={ field.options }
                                            name={ fieldKey }
                                            onChange={ handleInputChange }
                                            value={ formData[ fieldKey ] }
                                            subFields={ subField }
                                            disabled={ field.disabled }
                                        />
                                    </div>
                                );

                            } else {
                                console.log( field )
                            }
                        } )
                    }
                </div>
            </div>
        );
    }

    return (
        ! isLoading && appSettings && appSettings.id === app
        ?
            <div className={ 'cf7apps-body' }>
                <div className={ 'cf7apps-app-setting-header' }>
                    <div className={ 'cf7apps-container' }>
                        <h1>{ sprintf( __( '%s Settings', 'cf7apps' ), appSettings.title ) }</h1>
                    </div>
                </div>

                <div className={ 'cf7apps-app-setting-section' }>
                    <div className={ 'cf7apps-container' }>
                        <div className={ 'cf7apps-settings-content-wrapper' }>
                            <div className={ appSettings.is_enabled ? 'cf7apps-enabled-content' : 'cf7apps-disabled-content' }>
                                { Settings() }
                            </div>
                            { ! appSettings.is_enabled && (
                                <CF7AppsDisabledOverlay 
                                    appId={ appSettings.id } 
                                    appTitle={ appSettings.title } 
                                />
                            ) }
                        </div>
                    </div>
                </div>
            </div>
        :
            <div className={ 'cf7apps-body-loading' }>
                <CF7AppsSkeletonLoader height={ 800 } width={ '100%' } />
            </div>
    );
};

export default CF7AppsSettings;