import React, { useState } from 'react';
import SpaceChooserItem from './SpaceChooserItem';

const SpaceChooser = ({
                          currentSpaceImage,
                          noSpaceHtml,
                          canCreateSpace,
                          canAccessDirectory,
                          directoryUrl,
                          createSpaceUrl,
                      }) => {
    const [isOpen, setIsOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');

    return (
        <li className="nav-item dropdown">
            <a
                href="#"
                id="space-menu"
                className="nav-link dropdown-toggle"
                onClick={(e) => {
                    e.preventDefault();
                    setIsOpen(!isOpen);
                }}
                aria-expanded={isOpen}
            >
                {currentSpaceImage || noSpaceHtml}
            </a>
            <ul
                className="dropdown-menu"
                id="space-menu-dropdown"
                style={{ display: isOpen ? 'block' : 'none' }}
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
                        <SpaceChooserItem />
                    </div>
                </li>
                <li className="remoteSearch">
                    <div id="space-menu-remote-search" className="dropdown-item notLoaded hh-list"></div>
                </li>

                {canCreateSpace && (
                    <li>
                        <div className="dropdown-footer">
                            <a
                                href={createSpaceUrl}
                                className="btn btn-accent col-lg-12"
                            >
                                Create Space
                            </a>
                        </div>
                    </li>
                )}
            </ul>
        </li>
    );
};

export default SpaceChooser;
