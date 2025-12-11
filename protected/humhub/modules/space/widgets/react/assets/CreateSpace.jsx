import React, { useState } from 'react';
import axios from 'axios';
import omit from 'lodash/omit';

const CreateSpace = ({ onCancel, createSpaceUrl }) => {
    const [formData, setFormData] = useState({
        name: '',
        color: '#d1d1d1',
        description: '',
        visibility: '1',
        join_policy: '1'
    });
    const [errors, setErrors] = useState({});
    const [isAdvancedOpen, setIsAdvancedOpen] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);

    const visibilityOptions = [];
    const joinPolicyOptions = [];

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
        if (errors[name]) {
            setErrors(prev => omit(prev, name));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsSubmitting(true);
        setErrors({});

        try {
            const response = await axios.post(createSpaceUrl, {
                Space: formData
            });

            if (response.data.success) {
                // Redirect to the new space or refresh
                window.location.href = response.data.redirect || '/';
            } else if (response.data.errors) {
                setErrors(response.data.errors);
            }
        } catch (error) {
            console.error('Error creating space:', error);
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            } else {
                setErrors({ general: 'An error occurred while creating the space' });
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    const showJoinPolicy = formData.visibility !== '0';

    return (
        <div className="create-space-form">
            <div className="modal-header">
                <button
                    type="button"
                    className="close"
                    onClick={onCancel}
                    aria-label="Close"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 className="modal-title">
                    <strong>Create</strong> Space
                </h4>
            </div>

            <form onSubmit={handleSubmit}>
                <div className="modal-body">
                    {errors.general && (
                        <div className="alert alert-danger">{errors.general}</div>
                    )}

                    <div className="form-group">
                        <label htmlFor="space-name">Name</label>
                        <div className="input-group input-color-group">
                            <input
                                type="color"
                                name="color"
                                value={formData.color}
                                onChange={handleInputChange}
                                className="form-control"
                                style={{ maxWidth: '60px' }}
                            />
                            <input
                                type="text"
                                id="space-name"
                                name="name"
                                className={`form-control ${errors.name ? 'error' : ''}`}
                                placeholder="Space name"
                                maxLength="45"
                                value={formData.name}
                                onChange={handleInputChange}
                                autoFocus
                                required
                            />
                        </div>
                        {errors.name && (
                            <div className="help-block">{errors.name}</div>
                        )}
                    </div>

                    <div className="form-group">
                        <label htmlFor="space-description">Description</label>
                        <input
                            type="text"
                            id="space-description"
                            name="description"
                            className={`form-control ${errors.description ? 'error' : ''}`}
                            value={formData.description}
                            onChange={handleInputChange}
                        />
                        {errors.description && (
                            <div className="help-block">{errors.description}</div>
                        )}
                    </div>

                    <a
                        href="#"
                        onClick={(e) => {
                            e.preventDefault();
                            setIsAdvancedOpen(!isAdvancedOpen);
                        }}
                        style={{ fontSize: '11px' }}
                    >
                        <i className={`fa fa-caret-${isAdvancedOpen ? 'down' : 'right'}`}></i>
                        {' '}Advanced access settings
                    </a>

                    {isAdvancedOpen && (
                        <div style={{ marginTop: '15px' }}>
                            <div className="row">
                                <div className="col-lg-6">
                                    <div className="form-group">
                                        <label>Visibility</label>
                                        {visibilityOptions.map((label, value) => (
                                            <div key={value} className="radio">
                                                <label>
                                                    <input
                                                        type="radio"
                                                        name="visibility"
                                                        value={value}
                                                        checked={formData.visibility === value}
                                                        onChange={handleInputChange}
                                                    />
                                                    {' '}{label}
                                                </label>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                                {showJoinPolicy && (
                                    <div className="col-lg-6 spaceJoinPolicy">
                                        <div className="form-group">
                                            <label>Join Policy</label>
                                            {joinPolicyOptions.map((label, value) => (
                                                <div key={value} className="radio">
                                                    <label>
                                                        <input
                                                            type="radio"
                                                            name="join_policy"
                                                            value={value}
                                                            checked={formData.join_policy === value}
                                                            onChange={handleInputChange}
                                                        />
                                                        {' '}{label}
                                                    </label>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </div>

                <div className="modal-footer">
                    <button
                        type="button"
                        className="btn btn-default"
                        onClick={onCancel}
                        disabled={isSubmitting}
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        className="btn btn-primary"
                        disabled={isSubmitting}
                    >
                        {isSubmitting ? 'Creating...' : 'Next'}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default CreateSpace;
