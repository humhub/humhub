import React from 'react';

const SpaceChooserItem = ({
                              space,
                              visible = true,
                              updateCount = 0,
                          }) => {
    const hasUpdates = updateCount > 0;

    return (
        <a
            href={space.url}
            className={`dropdown-item d-flex${visible ? '' : ' d-none'}`}
        >
            {/* Space Image */}
            <div className="flex-shrink-0 me-2">
                <img
                    src={space.imageUrl || `/img/default_space.jpg`}
                    width="24"
                    height="24"
                    alt={space.name}
                    className="img-rounded"
                    style={{ borderRadius: '4px' }}
                />
            </div>

            <div className="flex-grow-1">
                <strong className="space-name">
                    {space.name}
                </strong>

                {space.badge}

                {hasUpdates && (
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
                    {space.description
                        ? space.description.length > 60
                            ? `${space.description.substring(0, 60)}…`
                            : space.description
                        : ''}
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
