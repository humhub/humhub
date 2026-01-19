import React, { useState, useEffect, useRef, useCallback } from 'react';
import axios from 'axios';
import _ from 'lodash';
import SpaceChooserItem from './SpaceChooserItem';
import CreateSpace from './CreateSpace';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['X-CSRF-Token'] = csrfToken;

const SpaceChooser = ({
                          currentSpaceImage,
                          canCreateSpace,
                          canAccessDirectory,
                          directoryUrl,
                          createSpaceUrl,
                          remoteSearchUrl,
                          spaces: initialSpaces,
                          visibilityOptions,
                          joinPolicyOptions
                      }) => {
    const [isOpen, setIsOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [spaces, setSpaces] = useState(initialSpaces);
    const [showCreateForm, setShowCreateForm] = useState(false);
    const dropdownRef = useRef(null);
    const dropdownToggleRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target) &&
                dropdownToggleRef.current && !dropdownToggleRef.current.contains(event.target)) {
                setIsOpen(false);
                setShowCreateForm(false);
            }
        };

        if (isOpen) {
            document.addEventListener('mousedown', handleClickOutside);
        }

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [isOpen]);

    const debouncedSearch = useCallback(
        _.debounce(async (query) => {
            try {
                const response = await axios.get(remoteSearchUrl, {
                    params: {
                        keyword: query,
                        target: 'chooser'
                    }
                });

                if (response.data) {
                    setSpaces(response.data);
                }
            } catch (error) {
                console.error('Error fetching spaces:', error);
            }
        }, 300),
        [remoteSearchUrl]
    );

    useEffect(() => {
        if (!searchQuery) {
            setSpaces(initialSpaces);
            return;
        }

        debouncedSearch(searchQuery);
    }, [searchQuery, initialSpaces, debouncedSearch]);

    return (
        <>
            <a
                ref={dropdownToggleRef}
                href="#"
                id="space-menu"
                className="nav-link dropdown-toggle"
                onClick={(e) => {
                    e.preventDefault();
                    setIsOpen(!isOpen);
                }}
                aria-expanded={isOpen}
            >
                {currentSpaceImage || (
                    <div className='no-space'>
                        <i className='fa fa-dot-circle-o'/>
                        <br/>
                        My spaces
                    </div>
                )}
            </a>
            {isOpen && (
                <ul
                    ref={dropdownRef}
                    className="dropdown-menu"
                    id="space-menu-dropdown"
                    style={{ display: 'block' }}
                >
                    <li>
                        <form
                            action=""
                            className="dropdown-header dropdown-controls"
                            onSubmit={(e) => e.preventDefault()}
                        >
                            <div className={canAccessDirectory ? 'input-group' : ''}>
                                <input
                                    type="text"
                                    id="space-menu-search"
                                    className="form-control"
                                    autoComplete="off"
                                    placeholder="Search for spaces"
                                    title="Search for spaces"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                />

                                {canAccessDirectory && (
                                    <span id="space-directory-link" className="input-group-text">
                  <a href={directoryUrl}>
                      <i className="fa fa-sitemap"></i>
                  </a>
                </span>
                                )}

                                <div
                                    className="search-reset"
                                    id="space-search-reset"
                                    style={{ display: searchQuery ? 'block' : 'none' }}
                                    onClick={() => setSearchQuery('')}
                                >
                                    <i className="fa fa-times-circle"></i>
                                </div>
                            </div>
                        </form>
                    </li>

                    <li>
                        <hr className="dropdown-divider" />
                    </li>
                    <li>
                        <div id="space-menu-spaces" className="notLoaded hh-list">
                            {spaces.map((space) => <SpaceChooserItem key={space.guid} space={space} />)}
                        </div>
                    </li>
                    <li className="remoteSearch">
                        <div id="space-menu-remote-search" className="dropdown-item notLoaded hh-list"></div>
                    </li>

                    {canCreateSpace && (
                        <li>
                            <div className="dropdown-footer">
                                <button
                                    type="button"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        setShowCreateForm(true);
                                    }}
                                    className="btn btn-accent col-lg-12"
                                >
                                    Create Space
                                </button>
                            </div>
                        </li>
                    )}
                </ul>
            )}
            {showCreateForm && (
                <div
                    ref={dropdownRef}
                    className="dropdown-menu"
                    style={{ display: 'block', width: '500px', maxWidth: '90vw' }}
                >
                    <CreateSpace
                        onCancel={() => {
                            setShowCreateForm(false);
                            setIsOpen(true);
                        }}
                        createSpaceUrl={createSpaceUrl}
                    />
                </div>
            )}
        </>
    );
};

export default SpaceChooser;
