const useState = wp.element.useState;
import Pagination from '../../../shared-components/pagination/Pagination';

const useMemo = wp.element.useMemo;
const {__} = wp.i18n;

let PageSize = 10;

const Chart = (props) => {

    //Pagination - START --------------------------------------------------------

    const [currentPage, setCurrentPage] = useState(1);

    const currentTableData = useMemo(() => {
        const firstPageIndex = (currentPage - 1) * PageSize;
        const lastPageIndex = firstPageIndex + PageSize;
        return props.data.slice(firstPageIndex, lastPageIndex);
    }, [currentPage, props.data]);

    //Pagination - END ----------------------------------------------------------

    function handleDataIcon(columnName) {

        return props.formData.sortingColumn === columnName ? props.formData.sortingOrder : '';

    }

    return (

        <div className="daextinma-data-table-container">

            <table className="daextinma-react-table__daextinma-data-table daextinma-react-table__daextinma-data-table-dashboard-menu">
                <thead>
                <tr>
                    <th>
                        <button
                            className={'daextinma-react-table__daextinma-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'post_title'}
                            data-icon={handleDataIcon('post_title')}
                        >{__('Post', 'daext-interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextinma-react-table__daextinma-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'post_date'}
                            data-icon={handleDataIcon('post_date')}
                        >{__('Date', 'daext-interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextinma-react-table__daextinma-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'post_type'}
                            data-icon={handleDataIcon('post_type')}
                        >{__('Type', 'daext-interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextinma-react-table__daextinma-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'content_length'}
                            data-icon={handleDataIcon('content_length')}
                        >{__('Length', 'daext-interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextinma-react-table__daextinma-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'manual_interlinks'}
                            data-icon={handleDataIcon('manual_interlinks')}
                        >{__('Int. Links', 'daext-interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextinma-react-table__daextinma-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'iil'}
                            data-icon={handleDataIcon('iil')}
                        >{__('Int. Inbound Links', 'daext-interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextinma-react-table__daextinma-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'recommended_interlinks'}
                            data-icon={handleDataIcon('recommended_interlinks')}
                        >{__('Recomm.', 'daext-interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextinma-react-table__daextinma-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'optimization'}
                            data-icon={handleDataIcon('optimization')}
                        >{__('Opt.', 'daext-interlinks-manager')}</button>
                    </th>
                </tr>
                </thead>
                <tbody>

                {currentTableData.map((row) => (
                    <tr key={row.id}>
                        <td>
                            <div className={'daextinma-react-table__post-cell-container'}>
                                <a href={row.post_permalink}>
                                    {row.post_title}
                                </a>
                                <a href={row.post_permalink} target={'_blank'} className={'daextinma-react-table__icon-link'}></a>
                                <a href={row.post_edit_link} className={'daextinma-react-table__icon-link'}></a>
                            </div>
                        </td>
                        <td>{row.formatted_post_date}</td>
                        <td>{row.post_type}</td>
                        <td>{row.content_length}</td>
                        <td>{row.manual_interlinks}</td>
                        <td>{row.iil}</td>
                        <td>{row.recommended_interlinks}</td>
                        <td>{row.optimization}</td>
                    </tr>
                ))}

                </tbody>
            </table>

            {props.data.length === 0 && <div
                className="daextinma-no-data-found">{__('We couldn\'t find any results matching your filters. Try adjusting your criteria.', 'daext-interlinks-manager')}</div>}
            {props.data.length > 0 &&
                <div className="daextinma-react-table__pagination-container">
                    <div className='daext-displaying-num'>{props.data.length + ' items'}</div>
                    <Pagination
                        className="pagination-bar"
                        currentPage={currentPage}
                        totalCount={props.data.length}
                        pageSize={PageSize}
                        onPageChange={page => setCurrentPage(page)}
                    />
                </div>
            }

        </div>

    );

};

export default Chart;
