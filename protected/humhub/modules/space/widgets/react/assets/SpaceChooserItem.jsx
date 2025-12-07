import React from 'react';

const SpaceChooserItem = ({
                              space,
                              updateCount = 0,
                              isFollowing,
                              isArchived,
                          }) => {
    return (
        <a
            href={space.url}
            className={`dropdown-item d-flex`}
        >
            <div className="flex-shrink-0 me-2" dangerouslySetInnerHTML={{__html: space.image}} />

            <div className="flex-grow-1">
                <strong className="space-name">
                    {space.name}
                </strong>

                {isFollowing && (
                    <i className="fa fa-star badge-space float-end type tt" title='You are following this space'/>
                )}

                {isArchived && (
                    <i className="fa fa-history badge-space float-end type tt" title='This space is archived'/>
                )}

                {updateCount > 0 && (
                    <div
                        data-message-count={updateCount}
                        className={`badge badge-space messageCount float-end tt`}
                        title={`${updateCount} new ${updateCount === 1 ? 'entry' : 'entries'} since your last visit`}
                    >
                        {updateCount}
                    </div>
                )}

                <br />

                <p className="space-description">
                    {space.description}
                </p>

                {space.tags && space.tags.length > 0 && (
                    <div className="space-tags d-none">
                        {space.tags.map((tag, index) => (
                            <span key={index} className="badge badge-light me-1">
                            {tag}
                          </span>
                        ))}
                    </div>
                )}
            </div>
        </a>
    );
};

export default SpaceChooserItem;
