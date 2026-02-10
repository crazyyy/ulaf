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

            <table className="daextinma-react-table__daextinma-data-table">
                <thead>
                <tr>
                    <th>{__('Post', 'daext-interlinks-manager')}</th>
                    <th>{__('Anchor Text', 'daext-interlinks-manager')}</th>
                    <th>{__('Juice', 'daext-interlinks-manager')}</th>
                </tr>
                </thead>
                <tbody>

                {currentTableData.map((row) => (
                    <tr key={row.id}>
                        <td>
                            <div className={'daextinma-react-table__post-cell-container'}>
                                <a href={row.postPermalink}>
                                    {row.postTitle}
                                </a>
                                <a href={row.postPermalink} target={'_blank'}
                                   className={'daextinma-react-table__icon-link'}></a>
                                <a href={row.postEditLink} className={'daextinma-react-table__icon-link'}></a>
                            </div>
                        </td>
                        <td>{row.anchor}</td>
                        <td>
                            <div className={'juice-relative-wrapper'}>
                                <div className="juice-relative-container">
                                    <div className="juice-relative" style={{width: row.juiceVisual + '%'}}></div>
                                </div>
                                <div className={'juice-value'}>{formatJuiceValue(row.juice)}</div>
                            </div>
                        </td>
                    </tr>
                ))}

                </tbody>
            </table>

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

        </div>

    );

};

export default Chart;
