const useState = wp.element.useState;
import Pagination from '../../../shared-components/pagination/Pagination';

const useMemo = wp.element.useMemo;
const {__} = wp.i18n;
import {downloadFileFromString} from '../../../utils/utils';

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

    /**
     * Download the file with the CSV data.
     */
    function downloadExportFile(url) {

        wp.apiFetch({
            path: '/interlinks-manager-pro/v1/anchors-menu-export-csv',
            method: 'POST',
            data: {
                url: url
            },
        }).then(response => {

                downloadFileFromString(response.csv_content, 'anchors');

            },
        );

    }

    function formatJuiceValue(juice) {

        if (juice > 1000000000000) {
            return (juice / 1000000000000).toFixed(1) + 'T';
        } else if (juice > 1000000000) {
            return (juice / 1000000000).toFixed(1) + 'B';
        } else if (juice > 1000000) {
            return (juice / 1000000).toFixed(1) + 'M';
        } else if (juice > 1000) {
            return (juice / 1000).toFixed(1) + 'K';
        } else{
            return parseInt(juice, 10).toFixed(0);
        }

    }

    return (

        <div className="daextinma-data-table-container">

            <table className="daextinma-react-table__daextinma-data-table daextinma-react-table__daextinma-data-table-juice-menu">
                <thead>
                <tr>
                    <th>
                        <button
                            className={'daextinma-react-table__daextinma-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'url'}
                            data-icon={handleDataIcon('url')}
                        >{__('URL', 'daext-interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextinma-react-table__daextinma-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'iil'}
                            data-icon={handleDataIcon('iil')}
                        >{__('Internal Inbound Links', 'daext-interlinks-manager')}</button>
                    </th>
                    <th>
                        <button
                            className={'daextinma-react-table__daextinma-sorting-button'}
                            onClick={props.handleSortingChanges}
                            value={'juice'}
                            data-icon={handleDataIcon('juice')}
                        >{__('Juice', 'daext-interlinks-manager')}</button>
                    </th>

                    <th></th>
                </tr>
                </thead>
                <tbody>

                {currentTableData.map((row) => (
                    <tr key={row.id}>
                        <td>
                            <div className={'daextinma-react-table__post-cell-container'}>
                                <a href={row.url}>{row.url}</a>
                                <a href={row.url} target={'_blank'}
                                   className={'daextinma-react-table__icon-link'}></a>
                            </div>
                        </td>
                        <td>{row.iil}</td>
                        <td>
                            <div className={'juice-relative-wrapper'}>
                                <div className="juice-relative-container">
                                    <div className="juice-relative" style={{width: row.juice_relative + '%'}}></div>
                                </div>
                                <div className={'juice-value'}>{formatJuiceValue(row.juice)}</div>
                            </div>
                        </td>
                        <td>
                            <div className={'button-actions-container'}>
                                <button
                                    className={'small-button'}
                                    onClick={() => props.urlDetailsViewHandler(row.id, row.url)}
                                >Details View
                                </button>
                                <button
                                    className={'small-button'}
                                    onClick={() => downloadExportFile(row.url)}
                                >Export
                                </button>
                            </div>
                        </td>
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
