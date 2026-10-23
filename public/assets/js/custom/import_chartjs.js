import {
    Chart,
    BarElement,
    ArcElement,
    LineElement,
    PointElement,
    BarController,
    DoughnutController,
    PieController,
    LineController,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend
  } from 'chart.js';

import ChartDataLabels from 'chartjs-plugin-datalabels';

Chart.register(
    BarElement,
    ArcElement,
    LineElement,
    PointElement,
    BarController,
    DoughnutController,
    PieController,
    LineController,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    ChartDataLabels
);